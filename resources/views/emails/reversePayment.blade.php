<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reversa de Transacción</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding-bottom: 20px;
        }
        .header {
            background: linear-gradient(90deg, #d9534f, #c9302c);
            color: #fff;
            text-align: center;
            padding: 30px 20px;
        }
        .header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px 20px;
            text-align: left;
        }
        .content p {
            line-height: 1.6;
            margin: 15px 0;
        }
        .details {
            background-color: #f9f9f9;
            border-left: 5px solid #d9534f;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .details h3 {
            color: #d9534f;
            margin-bottom: 5px;
        }
        .footer {
            background-color: #d9534f;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 0.9em;
            border-radius: 0 0 10px 10px;
        }
        .footer a {
            color: #fff;
            text-decoration: underline;
        }
        .button {
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://holandesa.universapps.com/storage/97/logo-holandesa-transparente.png" alt="Logo de {{ $name_business }}">
        <h1>Reversa de Transacción</h1>
    </div>
    <div class="content">
        <p>Estimado(a) <strong>{{ $nameClient }}</strong>,</p>
        <p>Te informamos que la transacción realizada en <strong>{{ $name_business }}</strong> ha sido anulada o revertida. A continuación, te detallamos la información de la transacción:</p>

        <div class="details">
            <h3>Información de la Transacción Revertida</h3>
            <p><strong>ID de Orden:</strong> {{ $transactions['id_order'] }}</p>
            <p><strong>Total Reembolsado:</strong> GTQ {{ number_format($transactions['total'], 2) }}</p>
            <p><strong>Fecha de la Transacción Original:</strong> {{ \Carbon\Carbon::parse($transactions['date_transaction'])->format('d/m/Y H:i:s') }}</p>
            <p><strong>Fecha de la Reversa:</strong> {{ \Carbon\Carbon::parse($transactions['date_reverse'])->format('d/m/Y H:i:s') }}</p>
            <p><strong>Método de Pago:</strong> {{ $transactions['payment'] }} ({{ ucfirst($transactions['type_card']) }})</p>
            <p><strong>Estado de la Transacción:</strong> Revertida</p>
        </div>

        <h3>Detalle del Producto:</h3>
        <p><strong>Descripción:</strong> {{ $product[0]['description'] }}</p>
        <p><strong>Cantidad:</strong> {{ $product[0]['quantity'] }}</p>
        <p><strong>Precio Unitario:</strong> GTQ - {{ number_format($product[0]['amount'], 2) }}</p>
        <p><strong>Subtotal:</strong> GTQ - {{ number_format($product[0]['subtotal'], 2) }}</p>

        <p>Puedes descargar el comprobante de la reversa haciendo clic en el siguiente botón:</p>
        <a href="{{ $transactions['url_voucher_reverse'] }}" target="_blank" class="button">Descargar Voucher</a>
    </div>

    <div class="footer">
        <p>Si tienes alguna duda o consulta sobre esta transacción, no dudes en contactarnos.</p>
        <p>&copy; {{ date('Y') }} {{ $name_business }}. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
