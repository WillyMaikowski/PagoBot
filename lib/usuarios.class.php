<?php

class Usuarios {

	static function get( $username ) {
		$link = mysqli_connect( 'localhost', '', '', 'DEUDABOT' );

		$ids = array_map( [ $link, 'escape_string' ], is_array( $username ) ? $username : [ trim( $username ) ] );
		$ids = implode( "', '", $username );

		$sql = "
select USER_ID id, USER_USERNAME username, USER_NAME nombre, USER_DATE date
from USERS
where USER_USERNAME in ( '$ids' )
or USER_ID in ( '$ids' )
";

		$res = mysqli_query( $link, $sql );
		if( ! $res ) {
			print_a( 'error', $sql, mysqli_error( $link ) );
			exit;
		}

		$usuarios = [];
		while( $row = $res->fetch_assoc() ) {
			$usuarios[ $row['id'] ] = $row;
		}

		return is_array( $username ) ? $usuarios : reset( $usuarios );
	}

	static function upd( $user ) {
		$link = mysqli_connect( 'localhost', '', '', 'DEUDABOT' );

		$user_id = (int) $user['id'];
		$username = mysqli_escape_string( $link, trim( $user['username'] ) );
		$name = $user['nombre'] ? $link->escape_string( $user['nombre'] ) : $link->escape_string( trim( $user['first_name'].' '.$user['last_name'] ) );

		$sql = "
insert into USERS( USER_ID, USER_USERNAME, USER_NAME, USER_DATE )
values( $user_id, '$username', '$name', now() ) on duplicate key update
USER_USERNAME = values( USER_USERNAME )
";
		if( $name ) $sql .= ", USER_NAME = values( USER_NAME )";

		$res = mysqli_query( $link, $sql );
		if( ! $res ) {
			print_a( 'error', $sql, mysqli_error( $link ) );
			exit;
		}

		return $user_id;
	}

}
