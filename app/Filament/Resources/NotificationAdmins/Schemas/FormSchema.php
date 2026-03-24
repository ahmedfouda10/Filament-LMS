<?php

namespace App\Filament\Resources\NotificationAdmins\Schemas;

use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;

class FormSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('user_id')->label('User')->searchable()->preload()
                ->options(User::pluck('name', 'id'))->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('body')->required(),
            Forms\Components\Select::make('type')->required()
                ->options(['enrollment' => 'Enrollment', 'payment' => 'Payment', 'certificate' => 'Certificate', 'quiz' => 'Quiz', 'subscription' => 'Subscription', 'system' => 'System']),
        ]);
    }
}
