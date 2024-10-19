<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-modal-submit-data-structure
 */
class ModalSubmitData
{
    /**
     * @param string $custom_id custom_id of the modal.
     * @param AbstractComponent[] $components Values submitted by the user.
     */
    public function __construct(public string $custom_id, public array $components)
    {
    }
}
