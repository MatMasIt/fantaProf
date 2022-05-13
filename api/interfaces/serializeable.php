<?php

interface ASerializable{
    public function deserialize(array $r): void;
    public function serialize(): array;
}