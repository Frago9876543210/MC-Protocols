<?php

declare(strict_types=1);

namespace Frago9876543210\RCON;


use pocketmine\utils\Binary;

class RCON{
	public const SERVER_DATA_AUTH = 3;
	public const SERVER_DATA_AUTH_RESPONSE = 2;
	public const SERVER_DATA_EXEC_COMMAND = 2;
	public const SERVER_DATA_RESPONSE_VALUE = 0;

	/** @var string $host */
	protected $host;
	/** @var int $port */
	protected $port;
	/** @var string $password */
	private $password;
	/** @var resource $socket */
	protected $socket;
	/** @var string $output */
	protected $output;

	/**
	 * RCON constructor.
	 * @param string $host
	 * @param int    $port
	 * @param string $password
	 * @param int    $timeout
	 * @throws \Exception
	 */
	public function __construct(string $host, int $port, string $password, int $timeout = 3){
		$this->host = $host;
		$this->port = $port;
		$this->password = $password;

		$this->socket = fsockopen($host, $port);
		if(!$this->socket){
			throw new \Exception("Could not create socket.");
		}
		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, true);
		if(!$this->authorize()){
			throw new \Exception("Wrong password!");
		}
	}

	/**
	 * @param string $data
	 */
	private function write(string $data) : void{
		fwrite($this->socket, $data, strlen($data));
	}

	/**
	 * @return Packet|null
	 */
	private function read() : ?Packet{
		$size = fread($this->socket, 4);
		if($size === false || strlen($size) < 4){
			return null;
		}
		$pk = new Packet($size . fread($this->socket, Binary::readLInt($size)));
		$pk->decode();
		return $pk;
	}

	/**
	 * @return bool
	 */
	private function authorize() : bool{
		$this->write(Packet::create($id = rand(), self::SERVER_DATA_AUTH, $this->password));
		if(($pk = $this->read()) === null){
			return false;
		}
		return $pk->id === $id && $pk->type === self::SERVER_DATA_AUTH_RESPONSE;
	}

	/**
	 * @param string $command
	 */
	public function executeCommand(string $command) : void{
		$this->write(Packet::create($id = rand(), self::SERVER_DATA_EXEC_COMMAND, $command));
		if(($pk = $this->read()) !== null && $pk->id === $id && $pk->type === self::SERVER_DATA_RESPONSE_VALUE){
			$this->output = $pk->body;
		}
	}

	/**
	 * @return string
	 */
	public function getOutput() : string{
		return $this->output;
	}

	/**
	 * Disconnect from server
	 */
	public function close() : void{
		if(is_resource($this->socket)){
			fclose($this->socket);
		}
	}
}
