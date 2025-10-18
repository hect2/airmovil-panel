<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra</title>
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
            background: linear-gradient(90deg, #3cbbbb, #1e7f7f);
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
            border-left: 5px solid #3cbbbb;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .details h3 {
            color: #3cbbbb;
            margin-bottom: 5px;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .product-table th, .product-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        .product-table th {
            background-color: #3cbbbb;
            color: white;
        }
        .footer {
            background-color: #3cbbbb;
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
        .voucher-link {
            text-align: center;
            margin-top: 20px;
        }
        .voucher-link a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3cbbbb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://holandesa.universapps.com/storage/97/logo-holandesa-transparente.png" alt="Logo de {{ $name_business }}">
        <h1>Confirmación de Compra</h1>
    </div>
    <div class="content">
        <p>Hola <strong>{{ $nameClient }}</strong>,</p>
        <p>Gracias por tu compra en <strong>{{ $name_business }}</strong>. Aquí tienes los detalles de tu transacción:</p>

        <div class="details">
            <h3>Información de la Transacción</h3>
            <p><strong>ID de Orden:</strong> {{ $transactions['id_order'] }}</p>
            <p><strong>Total Pagado:</strong> GTQ {{ number_format($transactions['total'], 2) }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($transactions['date_transaction'])->format('d/m/Y H:i:s') }}</p>
            <p><strong>Método de Pago:</strong> {{ $transactions['payment'] }} ({{ ucfirst($transactions['type_card']) }})</p>
            <p><strong>Estado:</strong> {{ $transactions['status_transaction'] }}</p>
        </div>

        <h3>Detalles del Producto</h3>
        <table class="product-table">
            <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($product as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>GTQ {{ number_format($item['amount'], 2) }}</td>
                    <td>GTQ {{ number_format($item['subtotal'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="voucher-link">
            <p>Puedes descargar tu comprobante de compra aquí:</p>
            <a href="{{ $transactions['url_voucher'] }}" target="_blank">Descargar Voucher</a>
        </div>
    </div>

    <div class="footer">
        <!-- <p>¿Necesitas ayuda? Contáctanos a <a href="mailto:soporte@holandesa.com">soporte@holandesa.com</a></p> -->
        <p>¿Necesitas ayuda? Contáctanos a <a href="mailto:soporte@holandesa.com">soporte@example.com</a></p>
        <p>&copy; {{ date('Y') }} {{ $name_business }}. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
