<?php

declare(strict_types=1);

namespace Frago9876543210\Query;


class Query{
	public const HANDSHAKE = 9;
	public const STATISTICS = 0;

	/** @var resource $socket */
	protected $socket;
	/** @var int $timestamp */
	protected $timestamp;

	/**
	 * Query constructor.
	 * @param string $host
	 * @param int    $port
	 * @param int    $timeout
	 * @throws \Exception
	 */
	public function __construct(string $host, int $port, int $timeout = 3){
		$this->socket = fsockopen("udp://" . $host, $port);
		if(!$this->socket){
			throw new \Exception("Could not create socket.");
		}
		$this->timestamp = time() + rand();
		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, true);
	}

	/**
	 * @param int    $request
	 * @param string $additional
	 */
	private function writeData(int $request, string $additional = "") : void{
		$data = pack("C3N", 0xfe, 0xfd, $request, $this->timestamp) . $additional;
		fwrite($this->socket, $data, strlen($data));
	}


	/**
	 * @return bool|string
	 */
	private function readData(){
		return substr(fread($this->socket, 4096), 5); //skip header & timestamp
	}

	/**
	 * @return null|string
	 */
	private function getToken() : ?string{
		$this->writeData(self::HANDSHAKE);
		return ($data = $this->readData()) === false ? null : $data;
	}


	/**
	 * @param string $token
	 * @return array|null
	 */
	private function getStatus(string $token) : ?array{
		$this->writeData(self::STATISTICS, pack("N", $token) . "\xff\xff\xff\x01");
		if(($data = $this->readData()) === false){
			return null;
		}
		if(count(($data = explode("\x00\x00\x01player_\x00\x00", substr($data, 11)))) !== 2){
			return null;
		}
		$statistics = [
			'players' => array_filter($data[1] === "\x00" ? [] : explode("\x00", $data[1]), function($v){
				return $v !== "";
			})
		];
		foreach(array_chunk(explode("\x00", $data[0]), 2) as $v){
			$statistics[$v[0]] = $v[1];
		}
		return $statistics;
	}

	/**
	 * @return array|null
	 */
	public function getResult() : ?array{
		return ($token = $this->getToken()) !== null && ($status = $this->getStatus($token)) !== null ? $status : null;
	}
}