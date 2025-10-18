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
         margin: 0;
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
                 <th>AIRMOVIL</th>
             </tr>


             <tr>
               <th>{!! $datosVoucher->ubicacion !!}</th>
            </tr>
            <tr>
               <th>AF {!! $datosVoucher->afiliacion !!} </th>
            </tr>


            <tr style="padding-top:5px !important">
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
                     Ref: {!! $datosVoucher->requestID !!}
                     <span class="thiz">
                        Aut:  {!! $datosVoucher->requestToken !!}
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
                  <strong> Compra: {!! $datosVoucher->moneda !!} {!!  number_format($datosVoucher->total, 2, '.', ',') !!}</strong>
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

            <tr>
               <th><strong>(01) PAGADO ELECTRÓNICAMENTE</strong></th>
            </tr>



               <tr class="process_neo_net">
                  <th> <small>Procesado con: NeoNet</small> </th>
               </tr>


         </table>

      </div>


</body>
</html>
