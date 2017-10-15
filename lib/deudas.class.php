<?php

class Deudas {

	static function get( $chat_id = 0, $msg_id = 0 ) {
		$link = mysqli_connect( 'localhost', '', '', 'DEUDABOT' );

		$cid = (int)$chat_id;
		$mid = (int)$msg_id;
		if( ! $cid && ! $mid ) return [];

		$sql = "
select DEU_MSG_ID msg_id, DEU_CHAT_ID chat_id, DEU_FROM_ID from_id, DEU_USER_ID to_id, DEU_Q q, DEU_DATE date, DEU_RESP resp
from DEUDAS
";

		$where = [];
		if( $cid ) $where[] = "DEU_CHAT_ID = $cid";
		if( $mid ) $where[] = "DEU_MSG_ID = $mid";
		$sql .= " where ".implode( ' and ', $where );

		$res = mysqli_query( $link, $sql );
		if( ! $res ) {
			print_a( 'error', $sql, mysqli_error( $link ) );
			exit;
		}

		$deudas = [];
		while( $row = $res->fetch_assoc() ) {
			$deudas[ $row['chat_id'] ][ $row['msg_id'] ][] = $row;
		}

		if( $mid ) foreach( $deudas as $ch => $d ) $deudas[$ch] = $d[$mid];

		return $cid ? $deudas[$cid] : $deudas;
	}

	static function set( $d ) {
		$link = mysqli_connect( 'localhost', '', '', 'DEUDABOT' );

		$msg_id = (int) $d['msg_id'];
		$chat_id = (int) $d['chat_id'];
		$user_from_id = (int) $d['from_id'];
		$user_to_id = (int) $d['to_id'];
		$q = (int) $d['q'];
		$date = (int) $d['date'];
		$resp = (int) $d['resp'];

		$sql = "
insert into DEUDAS ( DEU_MSG_ID, DEU_CHAT_ID, DEU_FROM_ID, DEU_USER_ID, DEU_Q, DEU_DATE, DEU_RESP )
values( $msg_id, $chat_id, $user_from_id, $user_to_id, $q, $date, $resp )
";

		$res = mysqli_query( $link, $sql );
		if( ! $res ) {
			print_a( 'error', $sql, mysqli_error( $link ) );
			exit;
		}

		return (int)mysqli_insert_id( $link );
	}

}
