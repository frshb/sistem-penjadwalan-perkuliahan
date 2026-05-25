<?php

namespace App\Services\GeneticAlgorithm;

class Chromosome
{
    /** @var Gene[] */
    public array $genes = [];
    public float $fitness = 0.0;

    public function __construct(array $genes = [])
    {
        $this->genes = $genes;
    }

    public function clone(): static
    {
        return new static(array_map(fn($g) => $g->clone(), $this->genes));
    }
}
