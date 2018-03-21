<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/18
 * Time: 17:21
 */

namespace App\Service;

use App\Exception\SocketException;
use Symfony\Component\Console\Exception\InvalidOptionException;

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

	// Config
	const MODULE_IP = 'module_ip';
	const MODULE_SUBNET_MASK = 'module_subnet_mask';
	const MODULE_GATEWAY = 'module_gateway';
	const MODULE_RESET_PERSISTENCE = 'module_reset_persistence';
	const MODULE_VERSION = 'module_version';
	const MODULE_SERIAL = 'module_id';
	const MODULE_CLOUD_DNS = 'module_cloud_dns';
	const MODULE_CLOUD_SERVER = 'module_cloud_server';
	const MODULE_CLOUD_STATE = 'module_cloud_state';
	const MODULE_CLOUD_PASSWORD = 'module_cloud_password';

	const SAVE_AND_RESTART_KEY = 7;

	const CONFIG = [
		self::MODULE_IP                => 2,
		self::MODULE_SUBNET_MASK       => 3,
		self::MODULE_GATEWAY           => 4,
		null                           => null,
		self::MODULE_RESET_PERSISTENCE => 6,
		self::MODULE_VERSION,
		self::MODULE_SERIAL,
		self::MODULE_CLOUD_DNS         => 8,
		self::MODULE_CLOUD_SERVER      => 9,
		self::MODULE_CLOUD_STATE       => 'A',
		self::MODULE_CLOUD_PASSWORD    => 'B',
	];

	const OK = '>OK;';
	const ERROR = '>ERR;';

	protected $rand;

	/**
	 * @param string $ip
	 *
	 * @return array
	 * @throws SocketException
	 */
	public function getConfig( $ip ) {
		if ( null === $this->rand ) {
			$this->rand = $this->getRandomNumber();
		}

		// Send config value request "#1" + random number on 4 digits
		// Explode with "," delimiter
		// Trim ">" and ";" that are at string beginning and ending
		$rawModuleData = explode(
			',',
			trim( $this->writeSocket(
				$ip,
				"#1{$this->rand};",
				static::RELAY_TCP_CONFIG_PORT
			), '>;' )
		);

		// Add dummy item to simulate password place (needed for array_combine)
		$rawModuleData[] = "";


		$moduleData = array_combine( array_keys( static::CONFIG ), $rawModuleData );

		// Remove last dummy item
		array_pop( $moduleData );

		return $moduleData;

	}


	/**
	 * @param string $ip
	 * @param array $param
	 *
	 * @throws SocketException
	 */
	public function setConfig( $ip, array $param ) {

		// Make et getConfig before to ensure module set acception
		$this->getConfig( $ip );

		if ( ! isset( static::CONFIG[ $param['name'] ] ) ) {
			throw new InvalidOptionException();
		}
		$configRequest = static::CONFIG[ $param['name'] ];


		// Set config
		$response = $this->writeSocket(
			$ip,
			"#{$configRequest}{$this->rand},{$param['value']};",
			static::RELAY_TCP_CONFIG_PORT
		);

		if ( static::OK !== $response ) {
			throw new SocketException( "Module response unexpected : $response" );
		}
	}

	/**
	 * @param $ip
	 */
	public function saveConfig($ip) {
		// Make et getConfig before to ensure module set acception
		$this->getConfig( $ip );


		$configRequest = static::SAVE_AND_RESTART_KEY;


		// Send restart command
		$response = $this->writeSocket(
			$ip,
			"#{$configRequest}{$this->rand};",
			static::RELAY_TCP_CONFIG_PORT
		);

		if ( static::OK !== $response ) {
			throw new SocketException( "Module response unexpected : $response" );
		}
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
	public function sendCommand( $ip, $channel, $state, $duration = null ) {

		$message = $state . $channel;

		if ( null !== $duration ) {
			$message .= ":{$duration}";
		}

		return $this->writeSocket( $ip, $message );
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
	protected function writeSocket( $ip, $message, $port = self::RELAY_TCP_COMMAND_PORT, $type = self::TCP ) {
		$socket = stream_socket_client( "{$type}://{$ip}:{$port}" );
		if ( $socket ) {
			$sent = stream_socket_sendto( $socket, $message );
			if ( $sent > 0 ) {
				$serverResponse = fread( $socket, 1024 );
				// Close socket before returning
				stream_socket_shutdown( $socket, STREAM_SHUT_RDWR );

				return $serverResponse;
			} else {
				$errorNumber = socket_last_error();
				throw new SocketException( socket_strerror( $errorNumber ), $errorNumber );
			}
		} else {
			$errorNumber = socket_last_error();
			throw new SocketException( socket_strerror( $errorNumber ), $errorNumber );
		}
	}

	/**
	 * @return int
	 */
	protected function getRandomNumber() {
		return ( rand( 0, 9999 ) );
	}
}