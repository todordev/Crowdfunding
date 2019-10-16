CREATE TABLE [jos_crowdf_comments] (
  'id'          INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'comment'     TEXT      NOT NULL,
  'record_date' TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'published'   INTEGER   NOT NULL             DEFAULT '0',
  'project_id'  INTEGER   NOT NULL,
  'user_id'     INTEGER   NOT NULL
);
CREATE TABLE [jos_crowdf_countries] (
  'id'        INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  'name'      TEXT    NOT NULL,
  'code'      TEXT    NOT NULL,
  'locale'    TEXT    NOT NULL,
  'latitude'  REAL,
  'longitude' REAL,
  'currency'  TEXT,
  'timezone'  TEXT
);
CREATE TABLE [jos_crowdf_currencies] (
  'id'       INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'    TEXT    NOT NULL,
  'code'     TEXT    NOT NULL,
  'symbol'   TEXT    NOT NULL,
  'position' INTEGER NOT NULL             DEFAULT '0'
);
CREATE TABLE [jos_crowdf_followers] (
  'user_id'    INTEGER NOT NULL,
  'project_id' INTEGER NOT NULL,
  PRIMARY KEY (user_id, project_id)
);
CREATE TABLE [jos_crowdf_intentions] (
  'id'          INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'user_id'     INTEGER   NOT NULL,
  'project_id'  INTEGER   NOT NULL,
  'reward_id'   INTEGER   NOT NULL,
  'record_date' TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE [jos_crowdf_locations] (
  'id'           INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  'name'         TEXT    NOT NULL,
  'latitude'     TEXT    NOT NULL,
  'longitude'    TEXT    NOT NULL,
  'country_code' TEXT    NOT NULL,
  'state_code'   TEXT    NOT NULL,
  'timezone'     TEXT    NOT NULL,
  'published'    INTEGER NOT NULL             DEFAULT '1'
);
CREATE TABLE [jos_crowdf_logs] (
  'id'          INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'       TEXT      NOT NULL,
  'data'        TEXT,
  'type'        TEXT      NOT NULL,
  'record_date' TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE [jos_crowdf_payment_sessions] (
  'id'           INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'user_id'      INTEGER   NOT NULL,
  'project_id'   INTEGER   NOT NULL,
  'reward_id'    INTEGER   NOT NULL,
  'record_date'  TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'unique_key'   TEXT      NOT NULL,
  'order_id'     TEXT      NOT NULL,
  'gateway'      TEXT      NOT NULL,
  'gateway_data' TEXT,
  'auser_id'     TEXT      NOT NULL,
  'session_id'   TEXT      NOT NULL,
  'intention_id' INTEGER   NOT NULL
);
CREATE TABLE [jos_crowdf_projects] (
  'id'            INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'         TEXT      NOT NULL,
  'alias'         TEXT      NOT NULL,
  'short_desc'    TEXT      NOT NULL,
  'description'   TEXT,
  'image'         TEXT      NOT NULL,
  'image_square'  TEXT      NOT NULL,
  'image_small'   TEXT      NOT NULL,
  'location_id'   INTEGER   NOT NULL             DEFAULT '0',
  'goal'          REAL      NOT NULL             DEFAULT '0.000',
  'funded'        REAL      NOT NULL             DEFAULT '0.000',
  'funding_type'  TEXT      NOT NULL             DEFAULT 'FIXED',
  'funding_start' DATE      NOT NULL             DEFAULT '1000-01-01',
  'funding_end'   DATE      NOT NULL             DEFAULT '1000-01-01',
  'funding_days'  INTEGER   NOT NULL             DEFAULT '0',
  'pitch_video'   TEXT      NOT NULL,
  'pitch_image'   TEXT      NOT NULL,
  'hits'          INTEGER   NOT NULL             DEFAULT '0',
  'created'       TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'featured'      INTEGER   NOT NULL             DEFAULT '0',
  'published'     INTEGER   NOT NULL             DEFAULT '0',
  'approved'      INTEGER   NOT NULL             DEFAULT '0',
  'ordering'      INTEGER   NOT NULL             DEFAULT '0',
  'params'        TEXT      NOT NULL             DEFAULT '{}',
  'catid'         INTEGER   NOT NULL             DEFAULT '0',
  'type_id'       INTEGER   NOT NULL             DEFAULT '0',
  'user_id'       INTEGER   NOT NULL
);
CREATE TABLE [jos_crowdf_reports] (
  'id'          INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'subject'     TEXT      NOT NULL,
  'description' TEXT,
  'email'       TEXT,
  'record_date' TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'user_id'     INTEGER   NOT NULL             DEFAULT '0',
  'project_id'  INTEGER   NOT NULL
);
CREATE TABLE [jos_crowdf_rewards] (
  'id'           INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'        TEXT    NOT NULL,
  'description'  TEXT    NOT NULL,
  'amount'       REAL    NOT NULL             DEFAULT '0.000',
  'number'       INTEGER NOT NULL             DEFAULT '0',
  'distributed'  INTEGER NOT NULL             DEFAULT '0',
  'delivery'     DATE    NOT NULL             DEFAULT '0000-00-00',
  'shipping'     INTEGER NOT NULL             DEFAULT '0',
  'image'        TEXT,
  'image_thumb'  TEXT,
  'image_square' TEXT,
  'published'    INTEGER NOT NULL             DEFAULT '1',
  'ordering'     INTEGER NOT NULL             DEFAULT '0',
  'project_id'   INTEGER NOT NULL
);
CREATE TABLE [jos_crowdf_transactions] (
  'id'               INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'txn_date'         TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'txn_amount'       REAL      NOT NULL             DEFAULT '0.000',
  'txn_currency'     TEXT      NOT NULL,
  'txn_status'       TEXT      NOT NULL             DEFAULT 'pending',
  'txn_id'           TEXT      NOT NULL,
  'parent_txn_id'    TEXT      NOT NULL,
  'extra_data'       TEXT,
  'status_reason'    TEXT      NOT NULL,
  'project_id'       INTEGER   NOT NULL,
  'reward_id'        INTEGER   NOT NULL             DEFAULT '0',
  'investor_id'      INTEGER   NOT NULL,
  'receiver_id'      INTEGER   NOT NULL,
  'service_provider' TEXT      NOT NULL,
  'service_alias'    TEXT      NOT NULL,
  'service_data'     BLOB,
  'reward_state'     INTEGER   NOT NULL             DEFAULT '0',
  'fee'              REAL      NOT NULL             DEFAULT '0.00'
);
CREATE TABLE [jos_crowdf_types] (
  'id'          INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'       TEXT    NOT NULL,
  'description' TEXT,
  'params'      TEXT
);
CREATE TABLE [jos_crowdf_updates] (
  'id'          INTEGER   NOT NULL PRIMARY KEY AUTOINCREMENT,
  'title'       TEXT      NOT NULL,
  'description' TEXT      NOT NULL,
  'record_date' TIMESTAMP NOT NULL             DEFAULT CURRENT_TIMESTAMP,
  'project_id'  INTEGER   NOT NULL,
  'user_id'     INTEGER   NOT NULL,
  'state'       INTEGER   NOT NULL             DEFAULT '0'
);
