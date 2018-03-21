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

	// Options
	const IP = 'ip';
	const CHANNEL = 'channel';
	const STATE = 'state';
	const DURATION = 'duration';

	// Protocols and ports
	const TCP = 'tcp';
	const UDP = 'udp';
	const RELAY_TCP_COMMAND_PORT = 6722;
	const RELAY_UDP_COMMAND_PORT = 6723;
	const RELAY_TCP_CONFIG_PORT = 5111;

	const MODULE_IP = 'module_ip';
	const MODULE_SUBNET_MASK = 'module_subnet_mask';
	const MODULE_GATEWAY = 'module_gateway';
	const MODULE_RESET_PERSISTENCE = 'module_reset_persistence';
	const MODULE_VERSION = 'module_version';
	const MODULE_SERIAL = 'module_id';
	const MODULE_CLOUD_DNS = 'module_cloud_dns';
	const MODULE_CLOUD_SERVER = 'module_cloud_server';
	const MODULE_CLOUD_STATE = 'module_cloud_state';

	const CONFIG = [
		0 => self::MODULE_IP,
		1 => self::MODULE_SUBNET_MASK,
		2 => self::MODULE_GATEWAY,
		3 => null,
		4 => self::MODULE_RESET_PERSISTENCE,
		5 => self::MODULE_VERSION,
		6 => self::MODULE_SERIAL,
		7 => self::MODULE_CLOUD_DNS,
		8 => self::MODULE_CLOUD_SERVER,
		9 => self::MODULE_CLOUD_STATE,
	];

	/**
	 * @param string $ip
	 *
	 * @return array
	 * @throws SocketException
	 */
	public function getConfig($ip) {
		$rand = $this->getRandomNumber();
		$rawModuleData = explode(
			',',
			trim($this->writeSocket(
				$ip,
				"#1{$rand};",
				static::RELAY_TCP_CONFIG_PORT
			), '>;')
		);

		return array_combine(static::CONFIG, $rawModuleData);
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
			if ($sent > 0) {
				$serverResponse = fread($socket, 1024);
				// Close socket before returning
				stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
				return $serverResponse;
			} else {
				$errorNumber = socket_last_error();
				throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
			}
		} else {
			$errorNumber = socket_last_error();
			throw new SocketException(socket_strerror( $errorNumber ), $errorNumber);
		}
	}

	/**
	 * @return int
	 */
	protected function getRandomNumber() {
		return(rand(0, 9999));
	}
}