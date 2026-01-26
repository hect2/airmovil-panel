<?php

namespace App\Console\Commands;

use App\Models\Liquidations;
use App\Models\sales\DetailTransactions;
use App\Models\sales\PaymentTransactions;
use App\Models\sales\SalesClients;
use App\Models\sales\TransactionFloat;
use App\Models\sales\Transactions;
use App\Services\Invoice\InvoiceService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateLiquidation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:liquidation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate liquidations for pending transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $aprox_decimals = 10;
        $transactions = Transactions::liquidatePendingTransactions();

        foreach ($transactions as $transaction) {
            $data = [];
            $items['items'] = [];
            $this->info("-----------------------------");
            $this->info(" ");
            $this->info("Transaction UUID: {$transaction->uuid} , amount: {$transaction->total}");

            $total_to_liquidate = $transaction->total;


            // Check for float transaction
            $float_transaction = TransactionFloat::where('transaction_uuid', $transaction->uuid)->first();
            if (!empty($float_transaction)) {
                $this->info("Float Transaction UUID: {$float_transaction->uuid} , amount: {$float_transaction->total}");
                $payment_transactions = PaymentTransactions::select(DB::raw('SUM(total_amount) as total_amount'))->where('transaction_uuid', $transaction->uuid)->where('approved', 1)->first();
                $this->info("Total Payment Transactions " . ($payment_transactions->total_amount ?? 0));
                $montoPayment = $payment_transactions->total_amount ?? 0;
                $total_to_liquidate = $total_to_liquidate + $montoPayment;
            }

            $this->info("Total to Liquidate for Transaction ID {$transaction->id}: {$total_to_liquidate}");

            $commission_percentage = env('LIQUIDATION_PORCENTAGE_COMISSION', 0);
            $commission_amount = env('LIQUIDATION_AMOUNT_COMISSION', 0);
            $this->info("Commission Percentage: {$commission_percentage} %");
            $this->info("Commission Amount: {$commission_amount}");

            $commission_value = ($total_to_liquidate * ($commission_percentage / 100)) + $commission_amount;
            $this->info("Commission Value for Transaction ID {$transaction->id}: {$commission_value}");


            $final_liquidation_amount = $total_to_liquidate - $commission_value;
            $this->info("Final Liquidation Amount for Transaction ID {$transaction->id}: {$final_liquidation_amount}");



            // Prepare data for invoice
            $totalMontoImpuesto = 0;
            $montoGravable = $final_liquidation_amount / 1.12;
            $montoImpuesto = $final_liquidation_amount - $montoGravable;
            $totalMontoImpuesto += $montoImpuesto;

            $items['items'][] = [
                'bienOServicio' => 'S',
                'cantidad' => 1,
                'unidadMedida' => 'UN',
                'descripcion' => 'Por Prestamos de servicios',
                'precioUnitario' => number_format((float) $final_liquidation_amount, $aprox_decimals, '.', ''),
                'precio' => number_format((float) $final_liquidation_amount, $aprox_decimals, '.', ''),
                'descuento' => 0,
                'total' => number_format((float) $final_liquidation_amount, $aprox_decimals, '.', ''),
                'impuestos' => [
                    [
                        "nombreCorto" => "IVA",
                        "codigoUnidadGravable" => 1,
                        "montoGravable" => number_format((float) $montoGravable, $aprox_decimals, '.', ''),
                        "montoImpuesto" => number_format((float) $montoImpuesto, $aprox_decimals, '.', ''),
                    ]
                ],
            ];
            $totalImpuestos = [
                [
                    "nombreCorto" => "IVA",
                    "totalMontoImpuesto" => number_format((float) $totalMontoImpuesto, $aprox_decimals, '.', ''),
                ]
            ];


            // Get sales client
            $sales_client = SalesClients::where('uuid', $transaction->client_uuid)->first();
            $nit = $sales_client->type_identifier == 'NIT' ? $sales_client->identifier : 'CF';
            $nombre = $sales_client->type_identifier == 'NIT' ? $sales_client->name : 'Consumidor Final';
            $correo = $sales_client->type_identifier == 'NIT' ? $sales_client->email : '';

            $data = [
                'nit' => $nit,
                'nombre' => $nombre,
                'correo' => $correo,
                'items' => $items['items'],
                'totalImpuestos' => $totalImpuestos,
                'granTotal' => $final_liquidation_amount,
            ];
            Log::info('data', [
                'data' => $data
            ]);


            if (!Liquidations::where('transaction_uuid', $transaction->uuid)->exists()) {
                // Handle case where liquidation already exists
                $liquidation = Liquidations::create([
                    'liquidation_uuid' => Str::uuid(),
                    'transaction_uuid' => $transaction->uuid,
                    'porcentage_commission' => $commission_percentage,
                    'amount_commission' => $commission_value,
                    'transaction_total' => $total_to_liquidate,
                    'total' => $final_liquidation_amount,
                ]);
            } else {
                $liquidation = Liquidations::where('transaction_uuid', $transaction->uuid)->first();
            }

            $transaction->liquidation_uuid = $liquidation->liquidation_uuid;
            $transaction->save();

            $invoiceService = new InvoiceService();
            $invoiceService->generateInvoice($liquidation->liquidation_uuid, $data);

            $invoiceService->consultPDF($liquidation->liquidation_uuid);
        }


        return Command::SUCCESS;
    }
}
