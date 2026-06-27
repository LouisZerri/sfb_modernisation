<?php

declare(strict_types=1);

namespace App\Service\Member;

use App\Entity\Member;
use App\Entity\Representative;
use Faker\Factory;
use Faker\Generator;

/**
 * Fabrique des adhérents fictifs mais réalistes (entreprise forestière + représentant) :
 * communes françaises réelles, SIRET valides (Luhn), téléphones et emails crédibles.
 */
final class MemberGenerator
{
    private const COMPANY_TYPES = [
        'EXPLOITATION FORESTIÈRE', 'SCIERIE', 'ETS', 'SARL', 'SAS', 'EURL',
        'TRANSPORTS BOIS', 'GF', 'NÉGOCE BOIS', 'TRAVAUX FORESTIERS',
    ];

    private const FOREST_WORDS = [
        'DU CHÊNE', 'DES PINS', 'DE LA FORÊT', 'DU HÊTRE', 'DES SAPINS',
        'DU BOIS VERT', 'DE LA SCIE', 'DES CHARMES', 'DU FRÊNE', 'DES ÉPICÉAS',
        'SYLVA', 'VERTBOIS', 'BOIS & FORÊTS', 'GRUMES', 'MERRAIN',
    ];

    private const EMAIL_DOMAINS = ['gmail.com', 'orange.fr', 'wanadoo.fr', 'free.fr', 'sfr.fr', 'laposte.net'];

    private readonly Generator $faker;

    /**
     * Noms d'entreprise déjà émis, pour garantir l'unicité sur tout le lot.
     *
     * @var array<string, true>
     */
    private array $usedNames = [];

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function generate(): Member
    {
        $lastName = $this->buildLastName();
        $firstName = $this->faker->firstName();

        $representative = (new Representative())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($this->buildEmail($firstName, $lastName));

        $commune = FrenchCommunes::random();

        return (new Member())
            ->setRepresentative($representative)
            ->setCompany($this->buildUniqueCompanyName($lastName, $commune))
            ->setAddress($this->faker->streetAddress())
            ->setPostalCode($commune['cp'])
            ->setCity($commune['ville'])
            ->setPhone($this->buildPhone())
            ->setSiret($this->buildSiret())
            ->setReturnedAt($this->buildReturnedAt());
    }

    /**
     * @param array{cp: string, ville: string} $commune
     */
    private function buildUniqueCompanyName(string $lastName, array $commune): string
    {
        $name = $this->buildCompanyName($lastName);

        // Différencie tant que le nom est déjà pris (mots forestiers, ville, n° de département).
        for ($attempt = 0; isset($this->usedNames[$name]) && $attempt < 6; ++$attempt) {
            $name = $this->differentiate($name, $commune);
        }

        $this->usedNames[$name] = true;

        return $name;
    }

    private function buildCompanyName(string $lastName): string
    {
        $type = self::COMPANY_TYPES[array_rand(self::COMPANY_TYPES)];
        $upperName = mb_strtoupper($lastName);
        $word = self::FOREST_WORDS[array_rand(self::FOREST_WORDS)];

        return match (random_int(0, 6)) {
            0 => \sprintf('%s %s', $upperName, $type),
            1 => \sprintf('%s %s', $type, $upperName),
            2 => \sprintf('ETS %s ET FILS', $upperName),
            3 => \sprintf('%s %s', $type, $word),
            4 => \sprintf('%s %s', $upperName, $word),
            5 => \sprintf('%s %s', $word, $upperName),
            default => \sprintf('%s BOIS', $upperName),
        };
    }

    /**
     * @param array{cp: string, ville: string} $commune
     */
    private function differentiate(string $name, array $commune): string
    {
        return match (random_int(0, 2)) {
            0 => \sprintf('%s %s', $name, self::FOREST_WORDS[array_rand(self::FOREST_WORDS)]),
            1 => \sprintf('%s %s', $name, mb_strtoupper($commune['ville'])),
            default => \sprintf('%s %s', $name, substr($commune['cp'], 0, 2)),
        };
    }

    /**
     * Nom de famille à plus forte variété : ~30 % de patronymes composés.
     */
    private function buildLastName(): string
    {
        if (random_int(1, 100) <= 30) {
            return \sprintf('%s-%s', $this->faker->lastName(), $this->faker->lastName());
        }

        return $this->faker->lastName();
    }

    private function buildEmail(string $firstName, string $lastName): string
    {
        return \sprintf(
            '%s.%s@%s',
            $this->slug($firstName),
            $this->slug($lastName),
            self::EMAIL_DOMAINS[array_rand(self::EMAIL_DOMAINS)],
        );
    }

    private function slug(string $value): string
    {
        $accents = ['à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c'];
        $normalized = mb_strtolower(strtr($value, $accents));

        return preg_replace('/[^a-z]/', '', $normalized) ?? '';
    }

    private function buildPhone(): string
    {
        $pairs = [];
        for ($i = 0; $i < 4; ++$i) {
            $pairs[] = \sprintf('%02d', random_int(0, 99));
        }

        return \sprintf('0%d %s', random_int(1, 9), implode(' ', $pairs));
    }

    /**
     * Génère un SIRET à 14 chiffres valide au sens de l'algorithme de Luhn.
     */
    private function buildSiret(): string
    {
        $digits = '';
        for ($i = 0; $i < 13; ++$i) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits.$this->luhnCheckDigit($digits);
    }

    private function luhnCheckDigit(string $partial): string
    {
        $sum = 0;
        $length = \strlen($partial);

        for ($i = 0; $i < $length; ++$i) {
            $digit = (int) $partial[$length - 1 - $i];

            // Le chiffre de contrôle se trouve en position paire (depuis la droite) : on double les rangs impairs ici.
            if (0 === $i % 2) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (string) ((10 - $sum % 10) % 10);
    }

    private function buildReturnedAt(): ?\DateTimeImmutable
    {
        // ~60 % des bulletins sont reçus, sur les 3 dernières années.
        if (random_int(1, 100) > 60) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-3 years', 'now'));
    }
}
