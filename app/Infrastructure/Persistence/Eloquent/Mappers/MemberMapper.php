<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Member;
use App\Infrastructure\Persistence\Eloquent\Models\MemberModel;

final class MemberMapper
{
    public static function toDomain(MemberModel $model): Member
    {
        return new Member(
            (string) $model->id,
            (string) $model->full_name,
            (string) $model->document_number
        );
    }

    public static function toModel(Member $entity): MemberModel
    {
        $model = MemberModel::find($entity->id()) ?? new MemberModel();
        $model->id = $entity->id();
        $model->full_name = $entity->fullName();
        $model->document_number = $entity->documentNumber();

        return $model;
    }
}
