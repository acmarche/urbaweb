<?php


namespace AcMarche\UrbaWeb;


class SortUtils
{
    public static function sortByLibelle(array $data): array
    {
        usort(
            $data,
            function ($itemA, $itemB) {
                {
                    if ($itemA->libelle == $itemB->libelle) {
                        return 0;
                    }

                    return ($itemA->libelle < $itemB->libelle) ? -1 : 1;
                }
            }
        );

        return $data;
    }
}
