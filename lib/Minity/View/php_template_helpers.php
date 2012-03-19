<?php

function out($data) {
    echo protect($data);
}

function protect($data)
{
    return htmlspecialchars((string)$data);
}

