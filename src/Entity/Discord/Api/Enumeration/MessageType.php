<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/message#message-object-message-types
 */
enum MessageType: int
{
    case DEFAULT = 0;
    case RECIPIENT_ADD = 1;
    case RECIPIENT_REMOVE = 2;
    case CALL = 3;
    case CHANNEL_NAME_CHANGE = 4;
    case CHANNEL_ICON_CHANGE = 5;
    case CHANNEL_PINNED_MESSAGE = 6;
    case USER_JOIN = 7;
    case GUILD_BOOST = 8;
    case GUILD_BOOST_TIER_1 = 9;
    case GUILD_BOOST_TIER_2 = 10;
    case GUILD_BOOST_TIER_3 = 11;
    case CHANNEL_FOLLOW_ADD = 12;
    case GUILD_DISCOVERY_DISQUALIFIED = 14;
    case GUILD_DISCOVERY_REQUALIFIED = 15;
    case GUILD_DISCOVERY_GRACE_PERIOD_INITIAL_WARNING = 16;
    case GUILD_DISCOVERY_GRACE_PERIOD_FINAL_WARNING = 17;
    case THREAD_CREATED = 18;
    case REPLY = 19;
    case CHAT_INPUT_COMMAND = 20;
    case THREAD_STARTER_MESSAGE = 21;
    case GUILD_INVITE_REMINDER = 22;
    case CONTEXT_MENU_COMMAND = 23;
    case AUTO_MODERATION_ACTION = 24;
    case ROLE_SUBSCRIPTION_PURCHASE = 25;
    case INTERACTION_PREMIUM_UPSELL = 26;
    case STAGE_START = 27;
    case STAGE_END = 28;
    case STAGE_SPEAKER = 29;
    case STAGE_TOPIC = 31;
    case GUILD_APPLICATION_PREMIUM_SUBSCRIPTION = 32;
    case GUILD_INCIDENT_ALERT_MODE_ENABLED = 36;
    case GUILD_INCIDENT_ALERT_MODE_DISABLED = 37;
    case GUILD_INCIDENT_REPORT_RAID = 38;
    case GUILD_INCIDENT_REPORT_FALSE_ALARM = 39;
    case PURCHASE_NOTIFICATION = 44;
    case POLL_RESULT = 46;
}
