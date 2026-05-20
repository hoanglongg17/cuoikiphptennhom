create database if not exists andiflashcarddb character set utf8mb4 collate utf8mb4_unicode_ci;
use andiflashcarddb;

-- ==========================================
-- bảng 1: users (tài khoản người dùng)
-- ==========================================
create table users (
    userid int auto_increment primary key,
    email varchar(255) unique not null,
    passwordhash varchar(255),
    googleid varchar(255),
    displayname varchar(100) not null,
    avatarurl varchar(500),
    currentstreak int default 0,
    isemailverified tinyint(1) default 0,
    verificationtoken varchar(100),
    role varchar(50) default 'user' comment 'user: người dùng thường, admin: quản trị viên',
    createdat datetime default current_timestamp,
    updatedat datetime null on update current_timestamp
) engine=innodb;

-- tạo index cho googleid
create unique index uq_users_googleid on users(googleid);
create index idx_users_role on users(role);

-- ==========================================
-- bảng 2: dailystats (thống kê theo ngày)
-- ==========================================
create table dailystats (
    statid int auto_increment primary key,
    userid int not null,
    studydate date not null,
    cardsstudied int default 0,
    timespentminutes int default 0,
    constraint fk_dailystats_users foreign key (userid) 
        references users(userid) on delete cascade
) engine=innodb;

-- ==========================================
-- bảng 3: decks (bộ thẻ)
-- ==========================================
create table decks (
    deckid int auto_increment primary key,
    userid int not null,
    name varchar(255) not null,
    description text,
    createdat datetime default current_timestamp,
    constraint fk_decks_users foreign key (userid) 
        references users(userid) on delete cascade,
	 constraint uq_user_deckname unique (userid, name) 
) engine=innodb;

-- ==========================================
-- bảng 4: decksettings (cài đặt của bộ thẻ)
-- ==========================================
create table decksettings (
    settingid int auto_increment primary key,
    deckid int unique not null,
    maxnewcardsperday int default 20,
    maxreviewsperday int default 200,
    constraint fk_decksettings_decks foreign key (deckid) 
        references decks(deckid) on delete cascade
) engine=innodb;

-- ==========================================
-- bảng 5: cards (thẻ ghi nhớ) - ĐÃ CẬP NHẬT USERID
-- ==========================================
create table cards (
    cardid int auto_increment primary key,
    userid int not null comment 'Chủ sở hữu của thẻ (Bắt buộc để phân biệt thẻ khi gỡ khỏi bộ)',
    deckid int null comment 'Cho phép null để thẻ có thể nằm trong kho chung mà không thuộc bộ nào',
    cardtype tinyint default 1 comment '1: Cơ bản, 2: Đảo ngược, 3: Nhập liệu',
    frontcontent text not null,
    backcontent text not null,
    pronunciation varchar(255) null,
    audiourl varchar(500) null,
    examplesentence text null,
    tags varchar(255) null,
    createdat datetime default current_timestamp,
    constraint fk_cards_users foreign key (userid) 
        references users(userid) on delete cascade,
    constraint fk_cards_decks foreign key (deckid) 
        references decks(deckid) on delete set null
) engine=innodb;

-- ==========================================
-- bảng 6: cardprogress (tiến độ học & thuật toán srs)
-- ==========================================
create table cardprogress (
    progressid int auto_increment primary key,
    cardid int unique not null,
    status tinyint default 0,
    duedate datetime default current_timestamp,
    intervaldays float default 0,
    easefactor float default 2.5,
    repetitions int default 0,
    lapses int default 0,
    constraint fk_cardprogress_cards foreign key (cardid) 
        references cards(cardid) on delete cascade
) engine=innodb;

-- ==========================================
-- bảng 7: reviewlogs (lịch sử ôn tập)
-- ==========================================
create table reviewlogs (
    logid int auto_increment primary key,
    cardid int not null,
    grade tinyint not null,
    reviewdate datetime default current_timestamp,
    durationms int default 0,
    constraint fk_reviewlogs_cards foreign key (cardid) 
        references cards(cardid) on delete cascade
) engine=innodb;

-- ==========================================
-- bảng 8: blogposts (bài viết trên blog)
-- ==========================================
create table blogposts (
    postid int auto_increment primary key,
    userid int not null,
    title varchar(255) not null,
    slug varchar(255) unique,
    content longtext not null,
    excerpt varchar(500),
    status varchar(20) default 'draft' comment 'draft: bản nháp, pending: chờ duyệt, published: đã đăng, archived: lưu trữ, denied: từ chối',
    views int default 0,
    sharedeckid int null comment 'ID của deck được chia sẻ trong bài viết (tùy chọn)',
    createdat datetime default current_timestamp,
    updatedat datetime null on update current_timestamp,
    publishedat datetime null,
    rejectionreason text null,
    is_pinned boolean default false comment 'Bài viết có được ghim hay không',
    constraint fk_blogposts_users foreign key (userid) 
        references users(userid) on delete cascade,
    constraint fk_blogposts_decks foreign key (sharedeckid) 
        references decks(deckid) on delete set null
) engine=innodb;
--ALTER TABLE blogposts
--ADD COLUMN rejectionreason TEXT NULL
--thêm hai dòng trên để thêm cột rejectionreason vào bảng blogposts nếu chưa có

create index idx_blogposts_status on blogposts(status);
create index idx_blogposts_userid on blogposts(userid);
create index idx_blogposts_publishedat on blogposts(publishedat);

-- ==========================================
-- bảng 8b: blogcategories (danh mục blog)
-- ==========================================
create table blogcategories (
    categoryid int auto_increment primary key,
    name varchar(100) not null unique,
    slug varchar(100) unique,
    description text,
    color varchar(20) default '#0066cc',
    createdat datetime default current_timestamp
) engine=innodb;

create index idx_blogcategories_slug on blogcategories(slug);

-- ==========================================
-- bảng 8c: blogtags (nhãn cho bài viết)
-- ==========================================
create table blogtags (
    tagid int auto_increment primary key,
    name varchar(50) not null unique,
    slug varchar(50) unique,
    usagecount int default 0,
    createdat datetime default current_timestamp
) engine=innodb;

create index idx_blogtags_slug on blogtags(slug);

-- ==========================================
-- bảng 8d: post_tags (liên kết post và tags)
-- ==========================================
create table post_tags (
    postid int not null,
    tagid int not null,
    primary key (postid, tagid),
    constraint fk_post_tags_posts foreign key (postid) 
        references blogposts(postid) on delete cascade,
    constraint fk_post_tags_tags foreign key (tagid) 
        references blogtags(tagid) on delete cascade
) engine=innodb;

-- ==========================================
-- bảng 8e: blogratings (đánh giá/like bài viết)
-- ==========================================
create table blogratings (
    ratingid int auto_increment primary key,
    postid int not null,
    userid int not null,
    rating tinyint not null comment '1-5 stars, hoặc 1 cho like',
    createdat datetime default current_timestamp,
    constraint fk_blogratings_posts foreign key (postid) 
        references blogposts(postid) on delete cascade,
    constraint fk_blogratings_users foreign key (userid) 
        references users(userid) on delete cascade
) engine=innodb;

create index idx_blogratings_postid on blogratings(postid);
create index idx_blogratings_userid on blogratings(userid);
create unique index uq_blogratings_postuser on blogratings(postid, userid);

-- ==========================================
-- bảng 8f: blog_nested_comments (bình luận lồng)
-- ==========================================
create table blog_nested_comments (
    commentid int auto_increment primary key,
    postid int not null,
    userid int not null,
    parentcommentid int null comment 'ID của comment cha (null nếu là top-level)',
    content text not null,
    status varchar(20) default 'pending' comment 'pending, approved, rejected',
    createdat datetime default current_timestamp,
    updatedat datetime null on update current_timestamp,
    constraint fk_blog_nested_comments_posts foreign key (postid) 
        references blogposts(postid) on delete cascade,
    constraint fk_blog_nested_comments_users foreign key (userid) 
        references users(userid) on delete cascade,
    constraint fk_blog_nested_comments_parent foreign key (parentcommentid) 
        references blog_nested_comments(commentid) on delete cascade
) engine=innodb;

create index idx_blog_nested_comments_postid on blog_nested_comments(postid);
create index idx_blog_nested_comments_status on blog_nested_comments(status);

-- ==========================================
-- bảng 8g: email_notifications (lịch sử thông báo email)
-- ==========================================
create table email_notifications (
    notificationid int auto_increment primary key,
    userid int not null,
    type varchar(50) not null comment 'comment_on_post, reply_on_comment, post_published',
    relatedpostid int null,
    relatedcommentid int null,
    subject varchar(255) not null,
    status varchar(20) default 'pending' comment 'pending, sent, failed',
    sendattempts int default 0,
    createdat datetime default current_timestamp,
    sentat datetime null,
    constraint fk_email_notifications_users foreign key (userid) 
        references users(userid) on delete cascade
) engine=innodb;

create index idx_email_notifications_status on email_notifications(status);
create index idx_email_notifications_type on email_notifications(type);

-- Thêm cột categoryid vào blogposts
alter table blogposts add column categoryid int null after sharedeckid;
alter table blogposts add constraint fk_blogposts_categories foreign key (categoryid) 
    references blogcategories(categoryid) on delete set null;

alter table blogposts add column if not exists rejectionreason text null after publishedat;

-- ==========================================
-- bảng 9: blogcomments (bình luận bài viết)
-- ==========================================
create table blogcomments (
    commentid int auto_increment primary key,
    postid int not null,
    userid int not null,
    content text not null,
    status varchar(20) default 'pending' comment 'pending: chưa duyệt, approved: được duyệt, rejected: từ chối',
    createdat datetime default current_timestamp,
    updatedat datetime null on update current_timestamp,
    constraint fk_blogcomments_posts foreign key (postid) 
        references blogposts(postid) on delete cascade,
    constraint fk_blogcomments_users foreign key (userid) 
        references users(userid) on delete cascade
) engine=innodb;

create index idx_blogcomments_postid on blogcomments(postid);
create index idx_blogcomments_status on blogcomments(status);

-- ==========================================
-- DỮ LIỆU MẪU
-- ==========================================

-- 1. Thêm người dùng (Giữ nguyên Hash Password của bạn)
insert into users (email, passwordhash, displayname, currentstreak, isemailverified, role)
values 
('admin@andi.com', '123456', '👨‍💼 Admin Master', 3, 1, 'admin'),
('nguyenvana@gmail.com', '123456', 'nguyễn văn a', 3, 1, 'user'), 
('tranthib@gmail.com', '123456', 'trần thị b', 0, 1, 'user');

-- 2. Thêm thống kê ngày
insert into dailystats (userid, studydate, cardsstudied, timespentminutes)
values 
(1, date_sub(current_date, interval 2 day), 20, 15), 
(1, date_sub(current_date, interval 1 day), 35, 20), 
(1, current_date, 10, 8);

-- 3. Thêm bộ thẻ
insert into decks (userid, name, description)
values 
(1, '100 từ vựng toeic', 'bộ từ vựng cơ bản thường gặp trong bài thi toeic'), 
(1, 'động từ bất quy tắc', 'cần học thuộc lòng để chia thì'),                 
(2, 'giải phẫu học cơ sở', 'dành cho sinh viên y khoa năm 1');

-- 4. Thêm cài đặt bộ thẻ
insert into decksettings (deckid, maxnewcardsperday, maxreviewsperday)
values (1, 20, 100), (2, 10, 50), (3, 30, 200);

-- 5. Thêm thẻ ghi nhớ (ĐÃ BỔ SUNG CỘT USERID CHO TỪNG THẺ)
insert into cards (userid, deckid, cardtype, frontcontent, backcontent, pronunciation, audiourl, examplesentence, tags)
values 
(1, 1, 1, 'accommodate (v)', 'cung cấp chỗ ở, đáp ứng nhu cầu', '/əˈkɒm.ə.deɪt/', '/audio/accommodate.mp3', 'the hotel can accommodate up to 500 guests.', 'toeic, verb'),
(1, 1, 1, 'fluctuate (v)', 'dao động, biến động', '/ˈflʌk.tʃu.eɪt/', '/audio/fluctuate.mp3', 'vegetable prices fluctuate according to the season.', 'toeic, verb'),
(1, 1, 1, 'mandatory (adj)', 'bắt buộc', '/ˈmæn.də.tər.i/', '/audio/mandatory.mp3', 'athletes must undergo a mandatory drug test.', 'toeic, adjective'),
(1, 1, 1, 'implement (v)', 'thi hành, thực hiện', '/ˈɪm.plɪ.mənt/', '/audio/implement.mp3', 'the changes to the national health system will be implemented next year.', 'toeic, verb'),
(1, 1, 1, 'crucial (adj)', 'quan trọng, cốt yếu', '/ˈkruː.ʃəl/', '/audio/crucial.mp3', 'her work has been crucial to the project\'s success.', 'toeic, adjective'),
(1, 1, 1, 'substantial (adj)', 'đáng kể, quan trọng', '/səbˈstæn.ʃəl/', '/audio/substantial.mp3', 'the findings show a substantial difference between the opinions of men and women.', 'toeic, adjective'),
(1, 1, 1, 'eliminate (v)', 'loại bỏ, loại trừ', '/ɪˈlɪm.ɪ.neɪt/', '/audio/eliminate.mp3', 'a move towards healthy eating could help eliminate heart disease.', 'toeic, verb'),
(1, 1, 1, 'initiate (v)', 'khởi xướng, bắt đầu', '/ɪˈnɪʃ.i.eɪt/', '/audio/initiate.mp3', 'who initiated the violence?', 'toeic, verb'),
(1, 2, 3, 'go', 'went - gone (đi)', '/ɡəʊ/', '/audio/go.mp3', 'i go to school every day.', 'verb, irregular'),
(1, 2, 3, 'see', 'saw - seen (nhìn thấy)', '/siː/', '/audio/see.mp3', 'i can see the ocean from my window.', 'verb, irregular'),
(1, 2, 3, 'take', 'took - taken (lấy, cầm, nắm)', '/teɪk/', '/audio/take.mp3', 'don\'t forget to take your umbrella.', 'verb, irregular'),
(1, 2, 3, 'eat', 'ate - eaten (ăn)', '/iːt/', '/audio/eat.mp3', 'i usually eat an apple for breakfast.', 'verb, irregular'),
(1, 2, 3, 'break', 'broke - broken (làm vỡ, bẻ gãy)', '/breɪk/', '/audio/break.mp3', 'he broke the vase by accident.', 'verb, irregular'),
(2, 3, 1, 'cranium', 'hộp sọ', null, null, 'the cranium protects the brain.', 'anatomy, bone'),
(2, 3, 1, 'clavicle', 'xương quai xanh', null, null, 'a broken clavicle is a common sports injury.', 'anatomy, bone'),
(2, 3, 1, 'femur', 'xương đùi', null, null, 'the femur is the longest bone in the human body.', 'anatomy, bone'),
(2, 3, 1, 'sternum', 'xương ức', null, null, 'the ribs are attached to the sternum.', 'anatomy, bone'),
(2, 3, 1, 'patella', 'xương bánh chè', null, null, 'the patella protects the knee joint.', 'anatomy, bone');

-- 6. Thêm tiến độ học tập
insert into cardprogress (cardid, status, duedate, intervaldays, easefactor, repetitions, lapses)
values 
(1, 2, date_add(now(), interval 4 day), 4.0, 2.6, 2, 0),  
(2, 1, now(), 1.0, 2.3, 1, 0),      
(3, 2, date_add(now(), interval 12 day), 12.0, 2.8, 3, 0),
(4, 1, date_add(now(), interval 1 day), 1.0, 2.5, 1, 0),  
(5, 0, now(), 0, 2.5, 0, 0),       
(6, 0, now(), 0, 2.5, 0, 0),
(7, 0, now(), 0, 2.5, 0, 0),
(8, 0, now(), 0, 2.5, 0, 0),
(9, 2, date_add(now(), interval 2 day), 2.0, 2.5, 2, 0),  
(10, 1, now(), 1.0, 2.3, 1, 0),     
(11, 0, now(), 0, 2.5, 0, 0),       
(12, 0, now(), 0, 2.5, 0, 0),
(13, 0, now(), 0, 2.5, 0, 0),
(14, 0, now(), 0, 2.5, 0, 0),       
(15, 0, now(), 0, 2.5, 0, 0),
(16, 0, now(), 0, 2.5, 0, 0),
(17, 0, now(), 0, 2.5, 0, 0),
(18, 0, now(), 0, 2.5, 0, 0);

-- 7. Thêm lịch sử ôn tập
insert into reviewlogs (cardid, grade, reviewdate, durationms)
values 
(1, 3, date_sub(now(), interval 4 day), 4500), 
(1, 4, date_sub(now(), interval 1 day), 1200), 
(2, 2, date_sub(now(), interval 1 day), 8000), 
(3, 3, date_sub(now(), interval 15 day), 3000), 
(3, 3, date_sub(now(), interval 10 day), 2500), 
(3, 4, date_sub(now(), interval 1 day), 1000), 
(4, 3, now(), 3500), 
(9, 3, date_sub(now(), interval 2 day), 2000),
(9, 3, date_sub(now(), interval 1 day), 1500),
(10, 1, date_sub(now(), interval 1 day), 5000);

-- 8. Thêm bài viết blog
insert into blogposts (userid, title, slug, content, excerpt, status, sharedeckid, publishedat)
values 
(2, 'Chia sẻ bộ từ vựng TOEIC hiệu quả', 'tu-vung-toeic-hieu-qua', 
'Học từ vựng TOEIC có thể rất khó khăn nhưng với bộ từ vựng được tuyển chọn kỹ lưỡng, bạn sẽ đạt được kết quả tốt hơn. Hôm nay mình chia sẻ bộ 100 từ vựng thường gặp nhất trong các đề thi TOEIC.', 
'Khám phá bộ từ vựng TOEIC được tuyển chọn kỹ lưỡng', 'published', 1, now()),
(2, 'Cách học động từ bất quy tắc dễ nhất', 'hoc-dong-tu-bat-quy-tac',
'Động từ bất quy tắc luôn là nỗi ám ảnh của những người học tiếng Anh. Trong bài viết này, mình sẽ chia sẻ một số mẹo giúp bạn nhớ những động từ này dễ dàng hơn.', 
'Mẹo giúp bạn dễ dàng nhớ các động từ bất quy tắc', 'published', 2, now()),
(1, 'Chia sẻ từ được sử dụng trong y học cơ sở', 'y-hoc-co-so-andi', 
'Sinh viên y khoa năm 1 thường gặp khó khăn trong việc học các thuật ngữ y học. Mình vừa tạo một bộ thẻ giúp ích cho việc học này.', 
'Dành cho sinh viên y khoa năm 1', 'draft', 3, null);

-- 9. Thêm bình luận blog
insert into blogcomments (postid, userid, content, status)
values 
(1, 1, 'Bộ từ vựng này rất hữu ích! Cảm ơn bạn đã chia sẻ', 'approved'),
(1, 3, 'Mình đang học TOEIC, bộ này giúp rất nhiều', 'approved'),
(2, 1, 'Tuyệt vời! Animated giải thích rất rõ ràng', 'approved');

-- 10. Thêm danh mục blog
insert into blogcategories (name, slug, description, color)
values 
('Từ Vựng Tiếng Anh', 'tu-voc-tieng-anh', 'Học từ vựng tiếng Anh cơ bản và nâng cao', '#FF6B6B'),
('Mẹo Học Tập', 'meo-hoc-tap', 'Chia sẻ mẹo và kỹ thuật học tập hiệu quả', '#4ECDC4'),
('Chia Sẻ Kinh Nghiệm', 'chia-se-kinh-nghiem', 'Chia sẻ kinh nghiệm học tập cá nhân', '#45B7D1');

-- 11. Thêm nhãn blog
insert into blogtags (name, slug, usagecount)
values 
('TOEIC', 'toeic', 1),
('English', 'english', 1),
('Learning Tips', 'learning-tips', 1);

-- 12. Cập nhật bài viết với danh mục
update blogposts set categoryid = 1 where postid = 1;
update blogposts set categoryid = 2 where postid = 2;
update blogposts set categoryid = 1 where postid = 3;

-- 13. Liên kết bài viết với nhãn
insert into post_tags (postid, tagid) values 
(1, 1), (1, 2),  -- Bài viết 1 có tag TOEIC và English
(2, 3),           -- Bài viết 2 có tag Learning Tips
(3, 2);           -- Bài viết 3 có tag English
