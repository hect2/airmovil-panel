<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Voucher</title>

   <style>

      .padd{
         padding-top: 0px;
      }

      body{
         font-family: 'Inconsolata', monospace !important;
         font-weight: 300;
         font-size: 105%;
         line-height: 22px;
         margin:0;
         padding: 0;
         height: 100vh;
	   }

      .container {
         width: 100%;
         height: 100vh;
         position: relative;
      }

      .container-table{
         padding: 1px 1px;
         height: auto;
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
      }

      .logo-pagalo{
         display: block;
         margin-left: auto;
         margin-right: auto;
         width: 50%;
         margin-bottom: 20px;
      }

      .process_neo_net{
         margin-top: 60px;
         padding: 5px;
         font-size: 85% !important;
      }

   </style>

</head>
<body>

   <div class="container">

      <table class="container-table">

          <tr>
              <th> <img class="logo-pagalo" src="images/theme/holandesa-blanco.jpg" alt="Imagen"> </th>
          </tr>

          <tr>
              <th>HOLANDESA</th>
          </tr>


          <tr>
            <th>{!! $datosVoucher->ubicacion !!} Guatemala</th>
         </tr>
         <tr>
            <th>AF {!! $datosVoucher->afiliacion !!} </th>
         </tr>

          <tr style="padding-top:5px !important;">
              <th style="padding-top: 15px;">
               <span>
                   AUDIT: {!! $datosVoucher->correlativo !!}
               </span>
              </th>
          </tr>
         <tr>
            <th>
               <span>
                 Guatemala, {!! $datosVoucher->fecha !!}
               </span>
            </th>
         </tr>
         <tr>
            <th>
               <span>
                  Ref: {!! $datosVoucher->reference !!}
                   <sapn class="thiz">No.{{$datosVoucher->number}}</sapn>
                  <span class="thiz">
                     Aut:{!! $datosVoucher->requestToken !!}
                  </span>
               </span>

            </th>
         </tr>
         <tr>
            <th>
               <span style="padding:2px" >
                  No.Tarjeta **** **** ****  {!! $datosVoucher->tarjeta !!}
               </span>
            </th>
         </tr>


         <tr class="padd">
            <th style="padding-top: 15px;">
               Compra: {!! $datosVoucher->moneda !!} -{!!  number_format($datosVoucher->total, 2, '.', ',') !!}
            </th>
         </tr>

         @if($datosVoucher->visacuotas)
            <tr>
               <th> Cuotas {!! $datosVoucher->visacuotas !!}  pagos</th>
            </tr>
         @endif


         <tr class="padd">
            <th style="padding-top: 15px;">{!! $datosVoucher->cliente !!} </th>
         </tr>
   {{--          <tr>--}}
   {{--              <th>(07) COMPROBANTE DE ANULACIÓN</th>--}}
   {{--          </tr>--}}
   {{--         <tr>--}}
   {{--            <th>(07) PAGADO ELECTRONICAMENTE!!</th>--}}
   {{--         </tr>--}}
   {{--         <tr>--}}
   {{--            <th>ACCEDO A PAGAR MONTO TOTAL INDICADO DE</th>--}}
   {{--         </tr>--}}

   {{--         <tr>--}}
   {{--            <th>ACUERDO AL CONTRATO FIRMADO CON EMISOR</th>--}}
   {{--         </tr>--}}
          <tr>
              <th style="border-bottom: 1px dotted gray;"></th>
          </tr>
          @if($datosVoucher->cardAID)
          <tr>
            <th>AID: {!! $datosVoucher->cardAID !!}</th>
         </tr>
          @endif
          <tr>
              <th>(01) PAGADO ELECTRÓNICAMENTE</th>
          </tr>

         @if ($datosVoucher->is_payfac)

            <tr class="process_neo_net">
               <th> <small>Procesado por Pagalo</small> </th>
            </tr>

         @else

            <tr class="process_neo_net">
               <th> <small>Procesado con: NeoNet</small> </th>
            </tr>

         @endif


      </table>

   </div>



</body>
</html>
