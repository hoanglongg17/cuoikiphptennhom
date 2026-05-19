<?php

use yii\db\Migration;

/**
 * Thêm cột is_pinned vào bảng blogposts để admin ghim bài viết
 */
class m260519_AddPinnedColumnToBlogPosts extends Migration
{
    public function safeUp()
    {
        $this->addColumn('blogposts', 'is_pinned', $this->boolean()->defaultValue(false)->comment('Bài viết có được ghim hay không'));
    }

    public function safeDown()
    {
        $this->dropColumn('blogposts', 'is_pinned');
    }
}
