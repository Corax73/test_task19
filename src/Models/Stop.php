<?php

namespace Models;

/**
 * @property string $table
 */
class Stop extends AbstractModel
{
    protected string $table = 'stops';
    public string $title;
    protected array $fillable = [
        'title'
    ];
    protected array $guarded = [];
    protected string $unique = '';
}
