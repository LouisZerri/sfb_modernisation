<?php

declare(strict_types=1);

namespace App\Service\Member;

/**
 * Échantillon de communes françaises réelles (code postal + ville),
 * utilisé pour générer des adresses crédibles. Couvre un large éventail
 * de départements à dominante rurale/forestière.
 *
 * @phpstan-type Commune array{cp: string, ville: string}
 */
final class FrenchCommunes
{
    /**
     * @var list<array{cp: string, ville: string}>
     */
    private const COMMUNES = [
        ['cp' => '01300', 'ville' => 'Belley'],
        ['cp' => '02000', 'ville' => 'Laon'],
        ['cp' => '03200', 'ville' => 'Vichy'],
        ['cp' => '04000', 'ville' => 'Digne-les-Bains'],
        ['cp' => '05000', 'ville' => 'Gap'],
        ['cp' => '07200', 'ville' => 'Aubenas'],
        ['cp' => '08000', 'ville' => 'Charleville-Mézières'],
        ['cp' => '09000', 'ville' => 'Foix'],
        ['cp' => '10000', 'ville' => 'Troyes'],
        ['cp' => '11000', 'ville' => 'Carcassonne'],
        ['cp' => '12000', 'ville' => 'Rodez'],
        ['cp' => '14000', 'ville' => 'Caen'],
        ['cp' => '15000', 'ville' => 'Aurillac'],
        ['cp' => '16000', 'ville' => 'Angoulême'],
        ['cp' => '17000', 'ville' => 'La Rochelle'],
        ['cp' => '18000', 'ville' => 'Bourges'],
        ['cp' => '19000', 'ville' => 'Tulle'],
        ['cp' => '21000', 'ville' => 'Dijon'],
        ['cp' => '22000', 'ville' => 'Saint-Brieuc'],
        ['cp' => '23000', 'ville' => 'Guéret'],
        ['cp' => '24000', 'ville' => 'Périgueux'],
        ['cp' => '25000', 'ville' => 'Besançon'],
        ['cp' => '26000', 'ville' => 'Valence'],
        ['cp' => '27000', 'ville' => 'Évreux'],
        ['cp' => '28000', 'ville' => 'Chartres'],
        ['cp' => '29000', 'ville' => 'Quimper'],
        ['cp' => '32000', 'ville' => 'Auch'],
        ['cp' => '33125', 'ville' => 'Saint-Magne'],
        ['cp' => '36000', 'ville' => 'Châteauroux'],
        ['cp' => '38000', 'ville' => 'Grenoble'],
        ['cp' => '39000', 'ville' => 'Lons-le-Saunier'],
        ['cp' => '40000', 'ville' => 'Mont-de-Marsan'],
        ['cp' => '41000', 'ville' => 'Blois'],
        ['cp' => '42000', 'ville' => 'Saint-Étienne'],
        ['cp' => '43000', 'ville' => 'Le Puy-en-Velay'],
        ['cp' => '46000', 'ville' => 'Cahors'],
        ['cp' => '48000', 'ville' => 'Mende'],
        ['cp' => '49000', 'ville' => 'Angers'],
        ['cp' => '50000', 'ville' => 'Saint-Lô'],
        ['cp' => '51000', 'ville' => 'Châlons-en-Champagne'],
        ['cp' => '52000', 'ville' => 'Chaumont'],
        ['cp' => '53000', 'ville' => 'Laval'],
        ['cp' => '54000', 'ville' => 'Nancy'],
        ['cp' => '55000', 'ville' => 'Bar-le-Duc'],
        ['cp' => '56000', 'ville' => 'Vannes'],
        ['cp' => '57000', 'ville' => 'Metz'],
        ['cp' => '58000', 'ville' => 'Nevers'],
        ['cp' => '61000', 'ville' => 'Alençon'],
        ['cp' => '63000', 'ville' => 'Clermont-Ferrand'],
        ['cp' => '64000', 'ville' => 'Pau'],
        ['cp' => '65000', 'ville' => 'Tarbes'],
        ['cp' => '70000', 'ville' => 'Vesoul'],
        ['cp' => '71000', 'ville' => 'Mâcon'],
        ['cp' => '72000', 'ville' => 'Le Mans'],
        ['cp' => '73000', 'ville' => 'Chambéry'],
        ['cp' => '74000', 'ville' => 'Annecy'],
        ['cp' => '76000', 'ville' => 'Rouen'],
        ['cp' => '79000', 'ville' => 'Niort'],
        ['cp' => '80000', 'ville' => 'Amiens'],
        ['cp' => '81000', 'ville' => 'Albi'],
        ['cp' => '85000', 'ville' => 'La Roche-sur-Yon'],
        ['cp' => '86000', 'ville' => 'Poitiers'],
        ['cp' => '87000', 'ville' => 'Limoges'],
        ['cp' => '88000', 'ville' => 'Épinal'],
        ['cp' => '89000', 'ville' => 'Auxerre'],
        ['cp' => '90000', 'ville' => 'Belfort'],
    ];

    /**
     * @return array{cp: string, ville: string}
     */
    public static function random(): array
    {
        return self::COMMUNES[array_rand(self::COMMUNES)];
    }
}
