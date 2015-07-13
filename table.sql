use drupal;

create table if not exists b2f_log (
  record_id integer not null auto_increment primary key,
  log_message varchar(200) null,
  failures varchar(1000) null,
  processed integer default null,
  process_method varchar(10) null,
  fedora_errors varchar(1000) null
);
