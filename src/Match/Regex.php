<?php

class Regex extends Condition
{
    protected function check($url): bool
    {
        return preg_match($this->condition, $url);
    }
}