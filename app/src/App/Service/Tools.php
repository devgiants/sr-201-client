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
	const TCP = 'tcp';
	const UDP = 'udp';
	const RELAY_TCP_COMMAND_PORT = 6722;
	const RELAY_UDP_COMMAND_PORT = 6723;
	const RELAY_TCP_CONFIG_PORT = 5111;


	/**
	 * @param string $ip
	 *
	 * @return string
	 * @throws SocketException
	 */
	public function getConfig($ip) {
		return $this->writeSocket($ip, "#1 5564;", static::RELAY_TCP_CONFIG_PORT);
	}


	/**
	 * @param string $ip
	 * @param string $channel
	 * @param bool|string $state
	 * @param int $duration
	 *
	 * @return string
	 * @throws SocketException
	 */
	public function sendCommand($ip, $channel, $state, $duration = null) {

		$message = $state . $channel;

		if(null !== $duration) {
			$message .= ":{$duration}";
		}

		return $this->writeSocket($ip, $message);
	}

	/**
	 * @param string $ip
	 * @param string $message
	 * @param int $port
	 * @param string $type
	 *
	 * @return string
	 * @throws SocketException
	 */
	protected function writeSocket($ip, $message, $port = self::RELAY_TCP_COMMAND_PORT, $type = self::TCP) {
		$socket = stream_socket_client("{$type}://{$ip}:{$port}");
		if ($socket) {
			$sent = stream_socket_sendto($socket, $message);
			stream_socket_shutdown($socket, STREAM_SHUT_RDWR);

//			if ($sent > 0) {
				$serverResponse = fread($socket, 1024);
				echo $serverResponse;
				return $serverResponse;
//			} else {
//				$errorNumber = socket_last_error();
//				throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
//			}
		} else {
			$errorNumber = socket_last_error();
			throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
		}
	}
}