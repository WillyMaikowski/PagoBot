<?php
require( 'config.php' );

TG::$token = '';

//print_a( TG::api( 'deleteWebhook' ) );
//print_a( TG::api( 'setWebhook', [ 'url' => 'https://apps.maikowski.cl/wh/deudabot.php' ] ) );exit;
/*
$offset = 186402283;
$updates = TG::api( 'getUpdates', [ 'offset' => $offset, 'allowed_updates' => [ 'message', 'edited_message', 'callback_query' ] ] );
$updates = (array) $updates['result'];
print_a( $updates );
*/

$req = json_decode( file_get_contents('php://input'), TRUE );
if( ! $req ) exit();
$updates = [ $req ];

foreach( $updates as $u ) {
	if( $u['callback_query'] ) {
		$u = $u['callback_query'];
		Usuarios::upd( $u['from'] );

		list( $cmd, $menciones, $textos ) = TG::msg( $u['message']['reply_to_message'] );
		if( ! $menciones[ $u['from']['username'] ] && ! $menciones[ $u['from']['id'] ] ) {
			TG::api( 'answerCallbackQuery', [
				'callback_query_id' => $u['id'],
				'text' => 'Solo las personas involucradas pueden contestar',
				'show_alert' => TRUE
			] );
			continue;
		}

		$resp = (int)$u['data'];
		$q = intval( $textos[0] );
		Deudas::set( [
			'msg_id' 	=> $u['message']['reply_to_message']['message_id'],
			'chat_id' => $u['message']['chat']['id'],
			'from_id' => $u['from']['id'],
			'to_id' 	=> $u['message']['reply_to_message']['from']['id'],
			'q' 			=> $q,
			'date' 		=> $u['message']['date'],
			'resp' 		=> $resp
		] );

		TG::api( 'answerCallbackQuery', [
			'callback_query_id' => $u['id'],
			'text' => $resp ? 'Haz confirmado el monto de $'.$q : 'Haz cancelado el monto que definio '.$u['from']['first_name'],
		] );

		$deudas = Deudas::get( $u['message']['chat']['id'], $u['message']['reply_to_message']['message_id'] );
		if( count( $deudas ) >= count( $menciones ) ) {
			$usuarios = [];
			foreach( $deudas as $d ) if( $d['resp'] ) $usuarios[$d['from_id']] = $d['from_id'];
			$usuarios = array_column( Usuarios::get( $usuarios ), 'username' );

			TG::api( 'editMessageText', [
				'chat_id' 			=> $u['message']['chat']['id'],
				'message_id' 		=> $u['message']['message_id'],
				'text' 					=> $usuarios ? 'Se ha agredado la deuda a '.implode( ' ', $usuarios ) : 'Nadie ha aceptado la deuda'
			] );
		}

		continue;
	}


	$u = (array)$u['message'] + (array)$u['edited_message'];
	if( ! $u['text'] ) continue;

	list( $cmd, $menciones, $textos ) = TG::msg( $u );
	if( ! $cmd || count( $cmd ) > 1 ) continue;
	$cmd = reset( $cmd );

	if( $cmd == '/medebes' && $menciones && count( $textos ) === 1 && intval( $textos[0] ) > 0 ) {
		TG::api( 'sendMessage', [
			'chat_id' => $u['chat']['id'],
			'text' => implode( ' ', $menciones ).'. Confirmas el monto?',
			'reply_to_message_id' => $u['message_id'],
			'reply_markup' => json_encode( [ 'inline_keyboard' => [ [ [ 'text' => 'Si', 'callback_data' => '1' ], [ 'text' => 'No', 'callback_data' => '0' ] ] ] ] )
		] );
		continue;
	}

	if( $cmd == '/resumen' ) {
		$deudas = (array)Deudas::get( $u['chat']['id'] );
		$usuarios = [];
		foreach( $deudas as $msgid => $t ) {
			$usuarios[$t['from_id']] = $t['from_id'];
			$usuarios[$t['to_id']] = $t['to_id'];
		}
		$usuarios = Usuarios::get( $usuarios );

		/*
		$img_antes_path = genImagen( $deudas, $usuarios );
		$deudas = reducirDeudas( $deudas );
		$img_despues_path = genImagen( $deudas, $usuarios );

		TG::api( 'sendPhoto', [
			'chat_id' => $u['chat']['id'],
			'photo' => 'https://apps.maikowski.cl/wh/'.$img_antes_path,
			'caption' => 'Antes'
		] );

		TG::api( 'sendPhoto', [
			'chat_id' => $u['chat']['id'],
			'photo' => 'https://apps.maikowski.cl/wh/'.$img_despues_path,
			'caption' => 'Despues'
		] );
		*/
		TG::api( 'sendMessage', [
			'chat_id' => $u['chat']['id'],
			'text' => 'Aun no hago este metodo :c '.json_encode( $deudas )
		] );

		continue;
	}

}

print_a( 'done!' );

function genImagen( $deudas, $usuarios ) {

}

function reducirDeudas( $deudas ) {

}
