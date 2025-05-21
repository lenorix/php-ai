<?php

namespace Lenorix\Ai\Chat;

enum CoreMessageRole: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case TOOL = 'tool';
}
