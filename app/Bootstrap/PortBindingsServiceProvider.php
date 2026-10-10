<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Ports\Outbound\BookRepositoryInterface;
use App\Application\Ports\Outbound\CategoryRepositoryInterface;
use App\Application\Ports\Outbound\ClockInterface;
use App\Application\Ports\Outbound\CoverStorageInterface;
use App\Application\Ports\Outbound\LoanRepositoryInterface;
use App\Application\Ports\Outbound\MemberRepositoryInterface;
use App\Application\Ports\Outbound\PasswordHasherInterface;
use App\Application\Ports\Outbound\ReportReaderInterface;
use App\Application\Ports\Outbound\TokenIssuerInterface;
use App\Application\Ports\Outbound\UnitOfWorkInterface;
use App\Application\Ports\Outbound\UserRepositoryInterface;
use App\Infrastructure\Clock\SystemUtcClock;
use App\Infrastructure\Persistence\Eloquent\LaravelUnitOfWork;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentBookRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentLoanRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentMemberRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentReportReader;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenIssuer;
use App\Infrastructure\Storage\LocalCoverStorage;
use Illuminate\Support\ServiceProvider;

final class PortBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BookRepositoryInterface::class, EloquentBookRepository::class);
        $this->app->singleton(MemberRepositoryInterface::class, EloquentMemberRepository::class);
        $this->app->singleton(LoanRepositoryInterface::class, EloquentLoanRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->singleton(ReportReaderInterface::class, EloquentReportReader::class);
        $this->app->singleton(UnitOfWorkInterface::class, LaravelUnitOfWork::class);
        $this->app->singleton(ClockInterface::class, SystemUtcClock::class);
        $this->app->singleton(PasswordHasherInterface::class, BcryptPasswordHasher::class);
        $this->app->singleton(TokenIssuerInterface::class, JwtTokenIssuer::class);
        $this->app->singleton(CoverStorageInterface::class, LocalCoverStorage::class);
    }
}
