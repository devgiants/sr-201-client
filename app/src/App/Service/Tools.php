<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/18
 * Time: 17:21
 */

namespace App\Service;

use App\Exception\SocketException;

class Tools {
	const RELAY_TCP_COMMAND_PORT = 6722;
	const RELAY_UDP_COMMAND_PORT = 6723;
	const RELAY_TCP_CONFIG_PORT = 5111;

	public function sendCommand($ip, $channel, $state) {

		// TODO add UDP connection ability
		$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		if ( ! $socket ) {
			$errorNumber = socket_last_error();
			throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
		}

		if ( ! socket_connect( $socket, $ip, static::RELAY_TCP_COMMAND_PORT ) ) {
			$errorNumber = socket_last_error();
			throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
		}
		// TODO add duration handling
		$message = $state . $channel;
		$length = strlen( $message );
		$sent   = socket_write( $socket, $message, $length );
		if ( false === $sent ) {
			$errorNumber = socket_last_error();
			throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
		} else if ( $length !== $sent ) {
			$msg = sprintf( 'only %d of %d bytes sent', $length, $sent );
			trigger_error( $msg, E_USER_NOTICE );
		}
	}
}