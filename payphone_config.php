<?php
// Token y StoreId de la app "Sama Shala - Web" (tipo Botón de Pago) en
// dash.payphone.app. Verificado con curl contra /api/button/Prepare: OK.
$payphone_token = 'djPCbG_BH9Ule_DRg5VwPq-1ZfOvBJj2wlAwHA9W9gNdWZK_cJCkXAGFhMxCFsDWF8E5rfNTcq1TtTw_YsfiCNdzm0n7clczUYHvMCX9WhJQZkMBUP5k_49HnZM25YEkE_WUTG3Km6qgFlZVb-Wf9cl73pIZqpBOOBNXu0sz5yKRe0Gwwu_XeL6uIzhI5r5Erp9F3Qf55OorAi6HXrwvs9RiRuRgJ-H8lG5LW0Jwis7Ucy5W_4e0G0PA_XsJ0s_B9U5vARPCMVePycM0h1rQuN8pt-W2N2j0ItcAw5RE92L7jCmFb0tE8TAqiAJMJ_PznrcjuA';

// Obligatorio para el Botón de Pago (viene en los datos de esa app).
$payphone_store_id = '07dd702b-22ce-48c7-8083-9289535c9b26';

// URL a la que PayPhone redirige al cliente tras el pago. Debe coincidir
// con el dominio público (no funciona con localhost).
$payphone_response_url = 'https://samashala.com/confirmar_pago_payphone.php';
