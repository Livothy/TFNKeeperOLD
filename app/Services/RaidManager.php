<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Raid\Raid;
use App\Models\Raid\RaidBoss;
use App\Models\User\User;
use App\Models\User\UserCurrency;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RaidManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raid Manager
    |--------------------------------------------------------------------------
    |
    | Handles interacting with the raid.
    |
    */

    /**********************************************************************************************

        RAID ATTACKS

    **********************************************************************************************/

    /**
     *  Checks if the user has the requirements to make an attack.
     *
     * @param User     $user
     * @param Raid     $raid
     * @param RaidBoss $boss
     *
     * @return array|null
     */
    public function pluckRequirements($user, $raid, $boss) {
        if (!$boss || !$raid) {
            return null;
        }
        if (!$raid->damage) {
            return null;
        }

        $userItems = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $user->id)->get();
        $userCurrencies = UserCurrency::with('currency')->where('quantity', '>', '0')->where('user_id', $user->id)->get();
        $plucked = [];

        foreach ($raid->attackAsset(0) as $key => $value) {
            if (!isset($value['asset'])) {
                continue;
            }

            $stacks = null;
            switch ($key) {
                case 'items':
                    $stacks = $userItems->where('item.id', $value['asset']->id);
                    break;
                case 'currencies':
                    $stacks = $userCurrencies->where('currency.id', $value['asset']->id);
                    break;
            }

            $quantityLeft = $value['quantity'] ?? 1;
            if ($stacks) {
                while ($quantityLeft > 0 && count($stacks) > 0) {
                    $stack = $stacks->pop();
                    switch ($key) {
                        case 'items':
                            $plucked[$stack->id] = $stack->count >= $quantityLeft ? $quantityLeft : $stack->count;
                            $userItems = $userItems->map(function ($s) use ($stack, $plucked) {
                                if ($s->id == $stack->id) {
                                    $s->count -= $plucked[$stack->id];
                                }
                                if ($s->count) {
                                    return $s;
                                } else {
                                    return null;
                                }
                            })->filter();
                            break;
                        case 'currencies':
                            if ($stack->quantity >= $quantityLeft) {
                                return true;
                            } else {
                                return null;
                            }
                            break;
                    }

                    $quantityLeft -= $plucked[$stack->id] ?? 0;
                }
            }

            if ($quantityLeft > 0) {
                return null;
            }
        }

        return $plucked;
    }

    /**
     *  Attacks the raid boss.
     *
     * @param User     $user
     * @param Raid     $raid
     * @param RaidBoss $boss
     *
     * @return array|null
     */
    public function attackBoss($raid, $boss, $user) {
        DB::beginTransaction();

        try {
            if (!$user) {
                throw new \Exception('Invalid user.');
            }
            if (!$boss) {
                throw new \Exception('Invalid '.__('raids.boss').'.');
            }
            if (!$raid) {
                throw new \Exception('Invalid '.__('raids.raid').'.');
            }
            if (!$raid->isActive) {
                throw new \Exception('This '.__('raids.raid').' is currently inactive!');
            }
            if (!$raid->damage) {
                throw new \Exception('This '.__('raids.raid').' lacks a method to attack it with...');
            }

            $plucked = $this->pluckRequirements($user, $raid, $boss);
            if (!$plucked) {
                throw new \Exception('You don\'t have the requirements needed to make an attack.');
            }

            switch (array_key_first($raid->attackAsset(0))) {
                case 'items':
                    $invManager = new InventoryManager;
                    foreach ($plucked as $id => $quantity) {
                        $itemStack = UserItem::find($id);
                        if (!$invManager->debitStack($user, 'Attacked '.ucfirst(__('raids.raid')), ['data' => 'Used to attack '.$boss->displayName.' encountered in '.$raid->displayName.'.'], $itemStack, $quantity)) {
                            throw new \Exception('Items could not be removed.');
                        }
                    }
                    break;
                case 'currencies':
                    $cManager = new CurrencyManager;
                    if (!$cManager->debitCurrency($user, null, 'Attacked '.ucfirst(__('raids.raid')), ('Used to attack '.$boss->displayName.' encountered in '.$raid->displayName.'.'), Currency::find($raid->attackAsset()['asset']->id), $raid->attackAsset()['quantity'])) {
                        throw new \Exception('Currency could not be debited.');
                    }
                    break;
            }

            $damageDone = $raid->damageDealt;
            $boss->damage += $damageDone;
            $boss->save();

            if (!$raid->raid_continue && $boss->damage >= $boss->health) {
                if ($raid->bosses->count() == 1) {
                    $raid->status = 2;
                    $raid->save();
                }
            }

            $dataString = 'Attacked and dealt '.$damageDone.' damage to '.$boss->displayName.' encountered in '.$raid->displayName;
            if (!$this->createLog($user->id, $raid->id, ucfirst(__('raids.raid')).' Attacked', $dataString, $damageDone)) {
                throw new \Exception('Failed to create '.__('raids.raid').' log.');
            }

            flash('You dealt '.$damageDone.' damage to the '.__('raids.boss').'.')->info();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a raid log.
     *
     * @param int    $userId
     * @param int    $raidId
     * @param string $type
     * @param string $data
     * @param int    $damage
     *
     * @return int
     */
    public function createLog($userId, $raidId, $type, $data, $damage) {
        return DB::table('raids_log')->insert(
            [
                'user_id'        => $userId,
                'raid_id'        => $raidId,
                'log'            => $type.($data ? ' ('.$data.')' : ''),
                'log_type'       => $type,
                'data'           => $data,
                'damage'         => $damage ?? null,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }
}
