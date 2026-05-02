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
    createdat datetime default current_timestamp
) engine=innodb;

-- tạo index cho googleid
create unique index uq_users_googleid on users(googleid);

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
        references users(userid) on delete cascade
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
-- DỮ LIỆU MẪU
-- ==========================================

-- 1. Thêm người dùng (Giữ nguyên Hash Password của bạn)
insert into users (email, passwordhash, displayname, currentstreak, isemailverified)
values 
('nguyenvana@gmail.com', '123456', 'nguyễn văn a', 3, 1), 
('tranthib@gmail.com', '123456', 'trần thị b', 0, 1);

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