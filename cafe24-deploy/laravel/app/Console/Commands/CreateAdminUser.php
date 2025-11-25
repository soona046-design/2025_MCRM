<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin
                            {login_id? : 로그인 ID (기본값: admin)}
                            {password? : 비밀번호 (기본값: admin123!@#)}
                            {--name=슈퍼관리자 : 사용자 이름}
                            {--email=admin@example.com : 이메일}
                            {--role=super_admin : 역할}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '관리자 계정을 생성합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loginId = $this->argument('login_id') ?? 'admin';
        $password = $this->argument('password') ?? 'admin123!@#';
        $name = $this->option('name');
        $email = $this->option('email');
        $role = $this->option('role');

        // 이미 존재하는지 확인
        $existingUser = User::where('login_id', $loginId)->first();
        if ($existingUser) {
            $this->error("사용자 '{$loginId}'는 이미 존재합니다.");

            if ($this->confirm('비밀번호를 업데이트하시겠습니까?')) {
                $existingUser->update([
                    'password' => Hash::make($password),
                ]);
                $this->info("'{$loginId}' 사용자의 비밀번호가 업데이트되었습니다.");
            }

            return 0;
        }

        // 새 사용자 생성
        $user = User::create([
            'user_id' => Str::uuid(),
            'login_id' => $loginId,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'active' => true,
            'clinic_id' => null,
        ]);

        $this->info("사용자 '{$loginId}'가 생성되었습니다!");
        $this->table(
            ['필드', '값'],
            [
                ['로그인 ID', $loginId],
                ['비밀번호', $password],
                ['이름', $name],
                ['이메일', $email],
                ['역할', $role],
            ]
        );

        return 0;
    }
}
