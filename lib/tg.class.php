<?php

class TG {

	public static $token = '';
	public static $url = 'https://api.telegram.org/bot';

	static function api( $cmd, $params = [] ) {
		$res = wget( self::$url.self::$token.'/'.$cmd, $params );
		if( ! $res ) {
			print_a( 'error', $cmd, $res );
			exit;
		}

		$res = json_decode( $res, TRUE );
		if( ! $res['ok'] ) {
			print_a( 'error', $cmd, $res );
			exit;
		}

		return $res;
	}


	static function msg( $msg	) {
		$cmd = [];
		$menciones = [];
		$t = $msg['text'];
		foreach( (array) $msg['entities'] as $e ) {
			if( $e['type'] == 'bot_command' ) {
				$cmd[] = substr( $msg['text'], $e['offset'], $e['length'] );
				for( $i = 0; $i < $e['length']; $i++ ) $t[ $i + $e['offset'] ] = ' ';
			}
			elseif( $e['type'] == 'mention' ) {
				$user = substr( $msg['text'], $e['offset'] + 1, $e['length'] );
				$menciones[ $user ] = $user;
				for( $i = 0; $i < $e['length']; $i++ ) $t[ $i + $e['offset'] ] = ' ';
			}
			elseif( $e['type'] == 'text_mention' ) {
				$menciones[ $e['user']['id'] ] = $e['user']['id'];
				for( $i = 0; $i < $e['length']; $i++ ) $t[ $i + $e['offset'] ] = ' ';
			}
		}

		$textos = preg_split( '/[\s,]+/', $t, 0, PREG_SPLIT_NO_EMPTY );

		return [ $cmd, $menciones, $textos ];
	}
}
