<?php

namespace App\Filament\Actions;

use App\Filament\Forms\UserForm;
use App\Models\User;
use App\Utils\LogUtil;
use App\Utils\NotificationUtil;
use Exception;
use Filament\Tables\Actions\Action;
use Illuminate\Auth\AuthenticationException;

class UserAction
{
    /**
     * 清除密码.
     */
    public static function resetPassword(): Action
    {
        return Action::make('清除密码')
            ->color('warning')
            ->icon('heroicon-o-lock-open')
            ->requiresConfirmation()
            ->action(function (User $user) {
                try {
                    $user->service()->changePassword('cat');
                    NotificationUtil::make(true, '已清除密码，用户可在下次登陆时自行设定密码');
                } catch (\Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 修改密码.
     */
    public static function changePassword(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('changePasswordAction')
            ->label('修改密码')
            ->form(UserForm::changePassword())
            ->action(function (array $data) {
                try {
                    if ($data['password'] != $data['password-verify']) {
                        throw new AuthenticationException('密码不一致');
                    }
                    /* @var User $user */
                    $user = auth()->user();
                    $user->service()->changePassword($data['password']);
                    NotificationUtil::make(true, '已修改密码');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }
}