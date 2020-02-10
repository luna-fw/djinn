<?php

namespace Luna\Djinn\Contracts;

interface ContainerConfigContract
{
    public function bind(string $wish, $granted):void;
    public function singleton(string $wish, $granted): void;
    public function contextual(string $who, string $wish, $granted): void;
    public function contextualSingleton(string $who, string $wish, $granted): void;

}
