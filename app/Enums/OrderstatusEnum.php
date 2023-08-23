<?php

namespace App\Enums;

enum OrderstatusEnum : string {

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case DECLINED = 'declined';

}