<?php namespace Interfaces;

interface Singleton
{
    public static function setInstance(self $instance): bool;
    public static function getInstance(): self;
}
