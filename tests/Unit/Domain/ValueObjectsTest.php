<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exceptions\InvalidDateRangeException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\ValueObjects\DateRange;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\Username;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_valid_isbn_is_accepted(): void
    {
        $isbn = new Isbn('9780307474728');
        $this->assertSame('9780307474728', $isbn->value());
    }

    public function test_isbn_with_invalid_length_or_characters_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        new Isbn('123456');
    }

    public function test_quantity_cannot_be_negative(): void
    {
        $this->expectException(ValidationException::class);
        new Quantity(-5);
    }

    public function test_date_range_valid_is_accepted(): void
    {
        $range = new DateRange('2026-10-01', '2026-10-31');
        $this->assertSame('2026-10-01', $range->fromString());
        $this->assertSame('2026-10-31', $range->toString());
    }

    public function test_date_range_with_end_before_start_throws_exception(): void
    {
        $this->expectException(InvalidDateRangeException::class);
        new DateRange('2026-10-31', '2026-10-01');
    }

    public function test_username_is_trimmed_and_lowercased(): void
    {
        $username = Username::fromString('  AnaGomez  ');
        $this->assertSame('anagomez', $username->value());
    }

    public function test_empty_username_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        Username::fromString('   ');
    }

    public function test_role_validates_admin_and_librarian(): void
    {
        $admin = Role::admin();
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isLibrarian());

        $librarian = Role::librarian();
        $this->assertTrue($librarian->isLibrarian());

        $this->expectException(ValidationException::class);
        new Role('superadmin');
    }
}
