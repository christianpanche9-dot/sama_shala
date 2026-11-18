<?php
// Token y StoreId de una app tipo "Botón de Pago" en dash.payphone.app
// (Configuración > +Agregar > Botón de Pago). No sirve el token de una
// app tipo "Api"/"Links de Pago": son productos distintos.
$payphone_token = '';

// Obligatorio para el Botón de Pago (viene en los datos de la app).
$payphone_store_id = '';

// URL a la que PayPhone redirige al cliente tras el pago. Debe coincidir
// con el dominio público (no funciona con localhost).
$payphone_response_url = 'https://samashala.com/confirmar_pago_payphone.php';
