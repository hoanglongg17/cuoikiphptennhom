<?php
/**
 * Website: http://www.yiiframework.com/
 * Yii 2 Migration File
 * Thêm các tính năng mở rộng cho Blog:
 * - Categories
 * - Tags
 * - Ratings/Likes
 * - Nested Comments
 * - Email Notifications
 */

use yii\db\Migration;

class m260516_BlogFeatures extends Migration
{
    public function up()
    {
        // ==========================================
        // bảng 10: blogcategories (danh mục blog)
        // ==========================================
        $this->createTable('blogcategories', [
            'categoryid' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'slug' => $this->string(100)->unique(),
            'description' => $this->text(),
            'color' => $this->string(20)->defaultValue('#0066cc'),
            'createdat' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_blogcategories_slug', 'blogcategories', 'slug');

        // ==========================================
        // bảng 11: blogtags (nhãn cho bài viết)
        // ==========================================
        $this->createTable('blogtags', [
            'tagid' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'slug' => $this->string(50)->unique(),
            'usagecount' => $this->integer()->defaultValue(0),
            'createdat' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_blogtags_slug', 'blogtags', 'slug');

        // ==========================================
        // bảng 12: post_tags (liên kết post và tags)
        // ==========================================
        $this->createTable('post_tags', [
            'postid' => $this->integer()->notNull(),
            'tagid' => $this->integer()->notNull(),
            'PRIMARY KEY (postid, tagid)',
        ]);

        $this->addForeignKey('fk_post_tags_posts', 'post_tags', 'postid', 'blogposts', 'postid', 'CASCADE');
        $this->addForeignKey('fk_post_tags_tags', 'post_tags', 'tagid', 'blogtags', 'tagid', 'CASCADE');

        // ==========================================
        // bảng 13: blogratings (đánh giá/like bài viết)
        // ==========================================
        $this->createTable('blogratings', [
            'ratingid' => $this->primaryKey(),
            'postid' => $this->integer()->notNull(),
            'userid' => $this->integer()->notNull(),
            'rating' => $this->tinyInteger()->notNull()->comment('1-5 stars, hoặc 1 cho like'),
            'createdat' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_blogratings_posts', 'blogratings', 'postid', 'blogposts', 'postid', 'CASCADE');
        $this->addForeignKey('fk_blogratings_users', 'blogratings', 'userid', 'users', 'userid', 'CASCADE');
        $this->createIndex('idx_blogratings_postid', 'blogratings', 'postid');
        $this->createIndex('idx_blogratings_userid', 'blogratings', 'userid');
        $this->createIndex('uq_blogratings_postuser', 'blogratings', ['postid', 'userid'], true);

        // ==========================================
        // bảng 14: blog_nested_comments (bình luận lồng)
        // ==========================================
        $this->createTable('blog_nested_comments', [
            'commentid' => $this->primaryKey(),
            'postid' => $this->integer()->notNull(),
            'userid' => $this->integer()->notNull(),
            'parentcommentid' => $this->integer()->null()->comment('ID của comment cha (null nếu là top-level)'),
            'content' => $this->text()->notNull(),
            'status' => $this->string(20)->defaultValue('pending')->comment('pending, approved, rejected'),
            'createdat' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updatedat' => $this->dateTime()->null()->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_blog_nested_comments_posts', 'blog_nested_comments', 'postid', 'blogposts', 'postid', 'CASCADE');
        $this->addForeignKey('fk_blog_nested_comments_users', 'blog_nested_comments', 'userid', 'users', 'userid', 'CASCADE');
        $this->addForeignKey('fk_blog_nested_comments_parent', 'blog_nested_comments', 'parentcommentid', 'blog_nested_comments', 'commentid', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_blog_nested_comments_postid', 'blog_nested_comments', 'postid');
        $this->createIndex('idx_blog_nested_comments_status', 'blog_nested_comments', 'status');

        // ==========================================
        // bảng 15: email_notifications (lịch sử thông báo email)
        // ==========================================
        $this->createTable('email_notifications', [
            'notificationid' => $this->primaryKey(),
            'userid' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull()->comment('comment_on_post, reply_on_comment, post_published'),
            'relatedpostid' => $this->integer()->null(),
            'relatedcommentid' => $this->integer()->null(),
            'subject' => $this->string(255)->notNull(),
            'status' => $this->string(20)->defaultValue('pending')->comment('pending, sent, failed'),
            'sendattempts' => $this->integer()->defaultValue(0),
            'createdat' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'sentat' => $this->dateTime()->null(),
        ]);

        $this->addForeignKey('fk_email_notifications_users', 'email_notifications', 'userid', 'users', 'userid', 'CASCADE');
        $this->createIndex('idx_email_notifications_status', 'email_notifications', 'status');
        $this->createIndex('idx_email_notifications_type', 'email_notifications', 'type');

        // ==========================================
        // thêm cột categoryid vào blogposts
        // ==========================================
        $this->addColumn('blogposts', 'categoryid', $this->integer()->null()->after('sharedeckid'));
        $this->addForeignKey('fk_blogposts_categories', 'blogposts', 'categoryid', 'blogcategories', 'categoryid', 'SET NULL');

        // ==========================================
        // DỮ LIỆU MẪU
        // ==========================================

        // Thêm categories
        $this->insert('blogcategories', [
            'name' => 'Từ Vựng Tiếng Anh',
            'slug' => 'tu-voc-tieng-anh',
            'description' => 'Học từ vựng tiếng Anh cơ bản và nâng cao',
            'color' => '#FF6B6B',
        ]);

        $this->insert('blogcategories', [
            'name' => 'Mẹo Học Tập',
            'slug' => 'meo-hoc-tap',
            'description' => 'Chia sẻ mẹo và kỹ thuật học tập hiệu quả',
            'color' => '#4ECDC4',
        ]);

        $this->insert('blogcategories', [
            'name' => 'Chia Sẻ Kinh Nghiệm',
            'slug' => 'chia-se-kinh-nghiem',
            'description' => 'Chia sẻ kinh nghiệm học tập cá nhân',
            'color' => '#45B7D1',
        ]);

        // Thêm tags mẫu
        $this->insert('blogtags', [
            'name' => 'TOEIC',
            'slug' => 'toeic',
            'usagecount' => 1,
        ]);

        $this->insert('blogtags', [
            'name' => 'English',
            'slug' => 'english',
            'usagecount' => 1,
        ]);

        $this->insert('blogtags', [
            'name' => 'Learning Tips',
            'slug' => 'learning-tips',
            'usagecount' => 1,
        ]);
    }

    public function down()
    {
        $this->dropForeignKey('fk_blogposts_categories', 'blogposts');
        $this->dropColumn('blogposts', 'categoryid');

        $this->dropTable('email_notifications');
        $this->dropTable('blog_nested_comments');
        $this->dropTable('blogratings');
        $this->dropTable('post_tags');
        $this->dropTable('blogtags');
        $this->dropTable('blogcategories');
    }
}
