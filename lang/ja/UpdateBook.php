<?php

return [
    'title.required' => 'タイトルは必須です。',
    'title.string' => 'タイトルは文字列で入力してください。',
    'title.max' => 'タイトルは255文字以内で入力してください。',

    'author.required' => '著者は必須です。',
    'author.string' => '著者は文字列で入力してください。',
    'author.max' => '著者は255文字以内で入力してください。',

    'isbn.required' => 'ISBNは必須です。',
    'isbn.digits' => 'ISBNは13桁の数字で入力してください。',
    'isbn.unique' => 'このISBNはすでに登録されています。',

    'published_date.required' => '出版日は必須です。',
    'published_date.date' => '出版日は正しい日付を入力してください。',

    'description.string' => '説明は文字列で入力してください。',
    'description.max' => '説明は1000文字以内で入力してください。',

    'image_url.url' => '画像URLの形式が正しくありません。',
    'image_url.max' => '画像URLは255文字以内で入力してください。',

    'genres.required' => 'ジャンルは必須です。',
    'genres.array' => 'ジャンルの形式が正しくありません。',
    'genres.min' => 'ジャンルを1つ以上選択してください。',
    'genres.*.exists' => '選択したジャンルが存在しません。',
];