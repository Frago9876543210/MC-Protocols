<?php

declare(strict_types=1);

namespace Frago9876543210\RCON;


use pocketmine\utils\BinaryStream;

class Packet extends BinaryStream{
	/** @var int $size */
	public $size;
	/** @var int $id */
	public $id;
	/** @var int $type */
	public $type;
	/** @var string $body */
	public $body;

	public function encode() : void{
		$this->reset();
		$this->putLInt($this->size);
		$this->putLInt($this->id);
		$this->putLInt($this->type);
		$this->put($this->body . "\x00\x00");
	}

	public function decode() : void{
		$this->offset = 0;
		$this->size = $this->getLInt();
		$this->id = $this->getLInt();
		$this->type = $this->getLInt();
		$this->body = substr($this->get(true), 0, -2);
	}

	public static function create(int $id, int $type, string $body) : string{
		$pk = new Packet;
		$pk->size = strlen($body) + 10;
		$pk->id = $id;
		$pk->type = $type;
		$pk->body = $body;
		$pk->encode();
		return $pk->buffer;
	}
}
