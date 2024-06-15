<?php

namespace Models;

class Comment implements \JsonSerializable
{
    private int $id;
    private int $recipeId;
    private int $userId;
    private string $content;
    private \DateTime $commentedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRecipeId(): int
    {
        return $this->recipeId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCommentedAt(): \DateTime
    {
        return $this->commentedAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setRecipeId(int $recipeId): void
    {
        $this->recipeId = $recipeId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setCommentedAt(\DateTime $commentedAt): void
    {
        $this->commentedAt = $commentedAt;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}