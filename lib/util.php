<?php

function print_a( $a ) {
	$id = 'debug_'.md5( rand() );
	if( func_num_args() > 1 ) $a = func_get_args();
	global $_print_a;
	if( ! headers_sent() ) UTIL::doctype();
	UTIL::flush( '<div class="debug"><a href="#" onclick="var p=document.getElementById(\''.$id.'\').style;p.display=p.display==\'none\'?\'block\':\'none\'">cerrar '.++$_print_a.'</a> <pre style="z-index:1000;border:1px solid red;align:left;font-weight:normal;padding:10px;margin:10px;background:white;color:black;font-family:courier new;font-size:10pt" id="'.$id.'">'.str_replace( '<', '&lt;', print_r( $a, TRUE ) )." \n".'</pre></div>'."\n" );
}

function wget( $url, $post = [], $headers = [] ) {
	$ch = curl_init();
	if( $headers ) curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT,        40000 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_URL,            $url );
	if( $post ) curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post ) );

	$res = curl_exec( $ch );
	curl_close( $ch );

	return $res;
}


class UTIL {

	static function flush( $str = null ) {
		$args = func_get_args();

		if( count( $args ) > 1 ) {
			call_user_func_array( 'printf', $args );
		}
		elseif( is_array( $str ) ) {
			print_a( $str );
		}
		elseif( $str !== null ) {
			print $str;
		}

		flush();
		ob_flush();
	}

	static function doctype() {
		print '<!DOCTYPE html>'."\n";
	}
}
