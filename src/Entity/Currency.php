<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Index(columns: ['numeric_code'])]
#[ORM\Index(columns: ['alpha_code'])]
#[ORM\Index(columns: ['code'])]
class Currency
{
    #[ORM\Id]
    #[ORM\Column(name: 'numeric_code', type: 'string', length: 3)]
    private string $numericCode = "";

    #[ORM\Column(name: 'alpha_code', type: 'string', length: 3)]
    private string $alphaCode = "";

    #[ORM\Column(name: 'code', type: 'string', length: 3)]
    private string $code = "";

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = "";

    #[ORM\Column(type: 'float')]
    private float $rate = 0.0;

    #[ORM\Column(type: 'float')]
    private float $inverseRate = 0.0;

    #[ORM\Column(type: 'datetime')]
    private DateTimeImmutable $date;

    public function __construct()
    {
        $this->date = new DateTimeImmutable();
    }

    public function getNumericCode(): string
    {
        return $this->numericCode;
    }

    public function setNumericCode(string $numericCode): void
    {
        $this->numericCode = $numericCode;
    }

    public function getAlphaCode(): string
    {
        return $this->alphaCode;
    }

    public function setAlphaCode(string $alphaCode): void
    {
        $this->alphaCode = $alphaCode;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setRate(float $rate): void
    {
        $this->rate = $rate;
    }

    public function getInverseRate(): float
    {
        return $this->inverseRate;
    }

    public function setInverseRate(float $inverseRate): void
    {
        $this->inverseRate = $inverseRate;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }
}