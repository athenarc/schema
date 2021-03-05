--
-- PostgreSQL database dump
--

-- Dumped from database version 12.5 (Ubuntu 12.5-0ubuntu0.20.04.1)
-- Dumped by pg_dump version 12.5 (Ubuntu 12.5-0ubuntu0.20.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: hist_type; Type: TYPE; Schema: public; Owner: schema
--

CREATE TYPE public.hist_type AS ENUM (
    'job',
    'workflow'
);


ALTER TYPE public.hist_type OWNER TO schema;

--
-- Name: visibility_types; Type: TYPE; Schema: public; Owner: schema
--

CREATE TYPE public.visibility_types AS ENUM (
    'private',
    'public'
);


ALTER TYPE public.visibility_types OWNER TO schema;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: auth_assignment; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.auth_assignment (
    item_name character varying(64) NOT NULL,
    user_id integer NOT NULL,
    created_at integer
);


ALTER TABLE public.auth_assignment OWNER TO schema;

--
-- Name: auth_item; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.auth_item (
    name character varying(64) NOT NULL,
    type integer NOT NULL,
    description text,
    rule_name character varying(64),
    data text,
    created_at integer,
    updated_at integer,
    group_code character varying(64)
);


ALTER TABLE public.auth_item OWNER TO schema;

--
-- Name: auth_item_child; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.auth_item_child (
    parent character varying(64) NOT NULL,
    child character varying(64) NOT NULL
);


ALTER TABLE public.auth_item_child OWNER TO schema;

--
-- Name: auth_item_group; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.auth_item_group (
    code character varying(64) NOT NULL,
    name character varying(255) NOT NULL,
    created_at integer,
    updated_at integer
);


ALTER TABLE public.auth_item_group OWNER TO schema;

--
-- Name: auth_rule; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.auth_rule (
    name character varying(64) NOT NULL,
    data text,
    created_at integer,
    updated_at integer
);


ALTER TABLE public.auth_rule OWNER TO schema;

--
-- Name: covid_dataset_application; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.covid_dataset_application (
    id bigint NOT NULL,
    email character varying(200),
    link text,
    description text,
    status smallint,
    username character varying(200),
    name text,
    submission_date timestamp without time zone
);


ALTER TABLE public.covid_dataset_application OWNER TO schema;

--
-- Name: covid_dataset_application_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.covid_dataset_application_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.covid_dataset_application_id_seq OWNER TO schema;

--
-- Name: covid_dataset_application_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.covid_dataset_application_id_seq OWNED BY public.covid_dataset_application.id;


--
-- Name: download_dataset; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.download_dataset (
    id integer NOT NULL,
    dataset_id text,
    provider text,
    user_id integer,
    folder_path text,
    date timestamp without time zone,
    name text,
    version text
);


ALTER TABLE public.download_dataset OWNER TO schema;

--
-- Name: download_dataset_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.download_dataset_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.download_dataset_id_seq OWNER TO schema;

--
-- Name: download_dataset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.download_dataset_id_seq OWNED BY public.download_dataset.id;


--
-- Name: helix_subjects; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.helix_subjects (
    id integer NOT NULL,
    name text
);


ALTER TABLE public.helix_subjects OWNER TO schema;

--
-- Name: helix_subjects_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.helix_subjects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.helix_subjects_id_seq OWNER TO schema;

--
-- Name: helix_subjects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.helix_subjects_id_seq OWNED BY public.helix_subjects.id;


--
-- Name: image_request; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.image_request (
    id integer NOT NULL,
    details text,
    user_name text,
    date timestamp without time zone,
    dock_link character varying(200)
);


ALTER TABLE public.image_request OWNER TO schema;

--
-- Name: image_request_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.image_request_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.image_request_id_seq OWNER TO schema;

--
-- Name: image_request_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.image_request_id_seq OWNED BY public.image_request.id;


--
-- Name: migration; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.migration (
    version character varying(180) NOT NULL,
    apply_time integer
);


ALTER TABLE public.migration OWNER TO schema;

--
-- Name: notification; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.notification (
    id bigint NOT NULL,
    recipient_id integer NOT NULL,
    message text,
    seen boolean DEFAULT false,
    type integer,
    created_at timestamp without time zone,
    read_at timestamp without time zone,
    url text
);


ALTER TABLE public.notification OWNER TO schema;

--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_id_seq OWNER TO schema;

--
-- Name: notification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.notification_id_seq OWNED BY public.notification.id;


--
-- Name: notification_recipient_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.notification_recipient_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_recipient_id_seq OWNER TO schema;

--
-- Name: notification_recipient_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.notification_recipient_id_seq OWNED BY public.notification.recipient_id;


--
-- Name: operation_locks; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.operation_locks (
    id text,
    operation text
);


ALTER TABLE public.operation_locks OWNER TO schema;

--
-- Name: ro_crate; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.ro_crate (
    id integer NOT NULL,
    username text,
    jobid text,
    date timestamp without time zone,
    software_url text,
    input text,
    publication text,
    output text
);


ALTER TABLE public.ro_crate OWNER TO schema;

--
-- Name: ro_crate_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.ro_crate_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ro_crate_id_seq OWNER TO schema;

--
-- Name: ro_crate_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.ro_crate_id_seq OWNED BY public.ro_crate.id;


--
-- Name: run_history; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.run_history (
    id bigint NOT NULL,
    username character varying(50),
    start timestamp without time zone,
    stop timestamp without time zone,
    command text,
    status character varying(50),
    imountpoint character varying(200),
    jobid character varying(20),
    softname character varying(100),
    softversion character varying(80),
    ram double precision,
    cpu integer,
    machinetype character varying,
    project character varying,
    max_ram double precision,
    max_cpu double precision,
    omountpoint character varying(200),
    iomountpoint character varying(200),
    software_id integer,
    mpi_proc_per_node integer,
    mpi_proc integer,
    type public.hist_type,
    field_values_json text,
    image text,
    remote_status_code integer
);


ALTER TABLE public.run_history OWNER TO schema;

--
-- Name: run_history_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.run_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.run_history_id_seq OWNER TO schema;

--
-- Name: run_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.run_history_id_seq OWNED BY public.run_history.id;


--
-- Name: software; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.software (
    id integer NOT NULL,
    name character varying(100),
    image character varying(200),
    script text,
    version character varying(80),
    uploaded_by character varying(255),
    visibility public.visibility_types,
    workingdir character varying(200),
    imountpoint character varying(200),
    description text,
    cwl_path character varying(150),
    has_example boolean DEFAULT false,
    biotools character varying(255),
    dois text,
    omountpoint character varying(200),
    mpi boolean DEFAULT false,
    covid19 boolean DEFAULT false,
    original_image character varying(200),
    docker_or_local boolean DEFAULT false,
    instructions text
);


ALTER TABLE public.software OWNER TO schema;

--
-- Name: software_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.software_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.software_id_seq OWNER TO schema;

--
-- Name: software_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.software_id_seq OWNED BY public.software.id;


--
-- Name: software_inputs; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.software_inputs (
    id bigint NOT NULL,
    name character varying(100),
    softwareid integer,
    "position" integer,
    field_type character varying(15),
    prefix character varying(50),
    default_value character varying(150),
    example character varying(150),
    optional boolean DEFAULT false,
    separate boolean DEFAULT true,
    enum_fields text,
    is_array boolean DEFAULT false,
    array_separator character varying(5) DEFAULT NULL::character varying,
    nested_array_binding boolean DEFAULT false
);


ALTER TABLE public.software_inputs OWNER TO schema;

--
-- Name: software_inputs_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.software_inputs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.software_inputs_id_seq OWNER TO schema;

--
-- Name: software_inputs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.software_inputs_id_seq OWNED BY public.software_inputs.id;


--
-- Name: software_upload; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.software_upload (
    id integer NOT NULL,
    name character varying(100),
    version character varying(80),
    image character varying(200),
    script text,
    uploaded_by character varying(255),
    date timestamp without time zone,
    visibility public.visibility_types,
    workingdir character varying(200),
    imountpoint character varying(200),
    description text,
    cwl_path character varying(150),
    biotools character varying(255),
    dois text,
    omountpoint character varying(200),
    mpi boolean DEFAULT false,
    original_image character varying(200),
    covid19 boolean DEFAULT false,
    docker_or_local boolean DEFAULT false,
    instructions text
);


ALTER TABLE public.software_upload OWNER TO schema;

--
-- Name: software_upload_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.software_upload_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.software_upload_id_seq OWNER TO schema;

--
-- Name: software_upload_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.software_upload_id_seq OWNED BY public.software_upload.id;


--
-- Name: ticket_body; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.ticket_body (
    id integer NOT NULL,
    id_head integer NOT NULL,
    name_user character varying(255),
    text text,
    client integer DEFAULT 0,
    date timestamp(0) without time zone
);


ALTER TABLE public.ticket_body OWNER TO schema;

--
-- Name: ticket_body_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.ticket_body_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_body_id_seq OWNER TO schema;

--
-- Name: ticket_body_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.ticket_body_id_seq OWNED BY public.ticket_body.id;


--
-- Name: ticket_file; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.ticket_file (
    id integer NOT NULL,
    id_body integer NOT NULL,
    "fileName" character varying(255) NOT NULL,
    document_name character varying(255)
);


ALTER TABLE public.ticket_file OWNER TO schema;

--
-- Name: ticket_file_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.ticket_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_file_id_seq OWNER TO schema;

--
-- Name: ticket_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.ticket_file_id_seq OWNED BY public.ticket_file.id;


--
-- Name: ticket_head; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.ticket_head (
    id integer NOT NULL,
    user_id integer NOT NULL,
    department character varying(255),
    topic character varying(255),
    status integer DEFAULT 0,
    date_update timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    page text
);


ALTER TABLE public.ticket_head OWNER TO schema;

--
-- Name: ticket_head_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.ticket_head_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_head_id_seq OWNER TO schema;

--
-- Name: ticket_head_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.ticket_head_id_seq OWNED BY public.ticket_head.id;


--
-- Name: upload_dataset_defaults; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.upload_dataset_defaults (
    id integer NOT NULL,
    provider text,
    provider_id text,
    default_community text,
    default_community_id text,
    name text,
    enabled boolean DEFAULT true
);


ALTER TABLE public.upload_dataset_defaults OWNER TO schema;

--
-- Name: upload_dataset_defaults_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.upload_dataset_defaults_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.upload_dataset_defaults_id_seq OWNER TO schema;

--
-- Name: upload_dataset_defaults_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.upload_dataset_defaults_id_seq OWNED BY public.upload_dataset_defaults.id;


--
-- Name: upload_dataset_helix; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.upload_dataset_helix (
    id integer NOT NULL,
    dataset_id text,
    provider text,
    user_id integer,
    api_key text,
    date timestamp without time zone,
    description text,
    license text,
    affiliation text,
    subject text,
    contact_email text,
    creator text,
    publication_doi text,
    private boolean,
    title text
);


ALTER TABLE public.upload_dataset_helix OWNER TO schema;

--
-- Name: upload_dataset_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.upload_dataset_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.upload_dataset_id_seq OWNER TO schema;

--
-- Name: upload_dataset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.upload_dataset_id_seq OWNED BY public.upload_dataset_helix.id;


--
-- Name: upload_dataset_zenodo; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.upload_dataset_zenodo (
    id integer NOT NULL,
    provider text,
    title text,
    creators text,
    upload_type text,
    publication_type text,
    image_type text,
    access_rights text,
    license text,
    access_conditions text,
    dataset_id text,
    date timestamp without time zone,
    embargo_date timestamp without time zone,
    api_key text,
    doi text,
    description text
);


ALTER TABLE public.upload_dataset_zenodo OWNER TO schema;

--
-- Name: upload_dataset_zenodo_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.upload_dataset_zenodo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.upload_dataset_zenodo_id_seq OWNER TO schema;

--
-- Name: upload_dataset_zenodo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.upload_dataset_zenodo_id_seq OWNED BY public.upload_dataset_zenodo.id;


--
-- Name: user; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    auth_key character varying(32) NOT NULL,
    password_hash character varying(255) NOT NULL,
    confirmation_token character varying(255),
    status integer DEFAULT 1 NOT NULL,
    superadmin smallint DEFAULT 0,
    created_at integer NOT NULL,
    updated_at integer NOT NULL,
    registration_ip character varying(15),
    bind_to_ip character varying(255),
    email character varying(128),
    email_confirmed smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public."user" OWNER TO schema;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO schema;

--
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.user_id_seq OWNED BY public."user".id;


--
-- Name: user_visit_log; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.user_visit_log (
    id integer NOT NULL,
    token character varying(255) NOT NULL,
    ip character varying(15) NOT NULL,
    language character(2) NOT NULL,
    user_agent character varying(255) NOT NULL,
    user_id integer,
    visit_time integer NOT NULL,
    browser character varying(30),
    os character varying(20)
);


ALTER TABLE public.user_visit_log OWNER TO schema;

--
-- Name: user_visit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.user_visit_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_visit_log_id_seq OWNER TO schema;

--
-- Name: user_visit_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.user_visit_log_id_seq OWNED BY public.user_visit_log.id;


--
-- Name: workflow; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.workflow (
    id bigint NOT NULL,
    name character varying(100),
    version character varying(80),
    location text,
    uploaded_by character varying(255),
    visibility public.visibility_types,
    description text,
    has_example boolean DEFAULT false,
    biotools character varying(255),
    dois text,
    covid19 boolean DEFAULT false,
    github_link text,
    original_file text,
    instructions text,
    visualize text
);


ALTER TABLE public.workflow OWNER TO schema;

--
-- Name: workflow_inputs; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.workflow_inputs (
    id bigint NOT NULL,
    name character varying(100),
    workflow_id integer,
    "position" integer,
    field_type character varying(15),
    prefix character varying(50),
    default_value character varying(150),
    example character varying(150),
    optional boolean DEFAULT false,
    separate boolean DEFAULT true,
    enum_fields text,
    is_array boolean DEFAULT false
);


ALTER TABLE public.workflow_inputs OWNER TO schema;

--
-- Name: workflow_inputs_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.workflow_inputs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.workflow_inputs_id_seq OWNER TO schema;

--
-- Name: workflow_inputs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.workflow_inputs_id_seq OWNED BY public.workflow_inputs.id;


--
-- Name: workflow_upload; Type: TABLE; Schema: public; Owner: schema
--

CREATE TABLE public.workflow_upload (
    id integer NOT NULL,
    name character varying(100),
    version character varying(80),
    location text,
    uploaded_by character varying(255),
    date timestamp without time zone,
    visibility public.visibility_types,
    description text,
    biotools character varying(255),
    dois text,
    covid19 boolean DEFAULT false,
    github_link text,
    original_file text,
    instructions text
);


ALTER TABLE public.workflow_upload OWNER TO schema;

--
-- Name: workflow_upload_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.workflow_upload_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.workflow_upload_id_seq OWNER TO schema;

--
-- Name: workflow_upload_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.workflow_upload_id_seq OWNED BY public.workflow_upload.id;


--
-- Name: workflows_id_seq; Type: SEQUENCE; Schema: public; Owner: schema
--

CREATE SEQUENCE public.workflows_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.workflows_id_seq OWNER TO schema;

--
-- Name: workflows_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: schema
--

ALTER SEQUENCE public.workflows_id_seq OWNED BY public.workflow.id;


--
-- Name: covid_dataset_application id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.covid_dataset_application ALTER COLUMN id SET DEFAULT nextval('public.covid_dataset_application_id_seq'::regclass);


--
-- Name: download_dataset id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.download_dataset ALTER COLUMN id SET DEFAULT nextval('public.download_dataset_id_seq'::regclass);


--
-- Name: helix_subjects id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.helix_subjects ALTER COLUMN id SET DEFAULT nextval('public.helix_subjects_id_seq'::regclass);


--
-- Name: image_request id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.image_request ALTER COLUMN id SET DEFAULT nextval('public.image_request_id_seq'::regclass);


--
-- Name: notification id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.notification ALTER COLUMN id SET DEFAULT nextval('public.notification_id_seq'::regclass);


--
-- Name: notification recipient_id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.notification ALTER COLUMN recipient_id SET DEFAULT nextval('public.notification_recipient_id_seq'::regclass);


--
-- Name: ro_crate id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ro_crate ALTER COLUMN id SET DEFAULT nextval('public.ro_crate_id_seq'::regclass);


--
-- Name: run_history id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.run_history ALTER COLUMN id SET DEFAULT nextval('public.run_history_id_seq'::regclass);


--
-- Name: software id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software ALTER COLUMN id SET DEFAULT nextval('public.software_id_seq'::regclass);


--
-- Name: software_inputs id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software_inputs ALTER COLUMN id SET DEFAULT nextval('public.software_inputs_id_seq'::regclass);


--
-- Name: software_upload id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software_upload ALTER COLUMN id SET DEFAULT nextval('public.software_upload_id_seq'::regclass);


--
-- Name: ticket_body id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_body ALTER COLUMN id SET DEFAULT nextval('public.ticket_body_id_seq'::regclass);


--
-- Name: ticket_file id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_file ALTER COLUMN id SET DEFAULT nextval('public.ticket_file_id_seq'::regclass);


--
-- Name: ticket_head id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_head ALTER COLUMN id SET DEFAULT nextval('public.ticket_head_id_seq'::regclass);


--
-- Name: upload_dataset_defaults id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_defaults ALTER COLUMN id SET DEFAULT nextval('public.upload_dataset_defaults_id_seq'::regclass);


--
-- Name: upload_dataset_helix id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_helix ALTER COLUMN id SET DEFAULT nextval('public.upload_dataset_id_seq'::regclass);


--
-- Name: upload_dataset_zenodo id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_zenodo ALTER COLUMN id SET DEFAULT nextval('public.upload_dataset_zenodo_id_seq'::regclass);


--
-- Name: user id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public."user" ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);


--
-- Name: user_visit_log id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.user_visit_log ALTER COLUMN id SET DEFAULT nextval('public.user_visit_log_id_seq'::regclass);


--
-- Name: workflow id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow ALTER COLUMN id SET DEFAULT nextval('public.workflows_id_seq'::regclass);


--
-- Name: workflow_inputs id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow_inputs ALTER COLUMN id SET DEFAULT nextval('public.workflow_inputs_id_seq'::regclass);


--
-- Name: workflow_upload id; Type: DEFAULT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow_upload ALTER COLUMN id SET DEFAULT nextval('public.workflow_upload_id_seq'::regclass);


--
-- Data for Name: upload_dataset_defaults; Type: TABLE DATA; Schema: public; Owner: schema
--

COPY public.upload_dataset_defaults (id, provider, provider_id, default_community, default_community_id, name, enabled) FROM stdin;
2	Helix			Helix	f
1	Zenodo			Zenodo	f
\.


--
-- Name: covid_dataset_application_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.covid_dataset_application_id_seq', 1, false);


--
-- Name: download_dataset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.download_dataset_id_seq', 1, true);


--
-- Name: helix_subjects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.helix_subjects_id_seq', 1410, true);


--
-- Name: image_request_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.image_request_id_seq', 1, false);


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.notification_id_seq', 20, true);


--
-- Name: notification_recipient_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.notification_recipient_id_seq', 1, false);


--
-- Name: ro_crate_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.ro_crate_id_seq', 3, true);


--
-- Name: run_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.run_history_id_seq', 1169, true);


--
-- Name: software_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.software_id_seq', 76, true);


--
-- Name: software_inputs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.software_inputs_id_seq', 445, true);


--
-- Name: software_upload_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.software_upload_id_seq', 75, true);


--
-- Name: ticket_body_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.ticket_body_id_seq', 7, true);


--
-- Name: ticket_file_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.ticket_file_id_seq', 1, false);


--
-- Name: ticket_head_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.ticket_head_id_seq', 8, true);


--
-- Name: upload_dataset_defaults_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.upload_dataset_defaults_id_seq', 2, true);


--
-- Name: upload_dataset_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.upload_dataset_id_seq', 1, true);


--
-- Name: upload_dataset_zenodo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.upload_dataset_zenodo_id_seq', 1, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.user_id_seq', 41, true);


--
-- Name: user_visit_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.user_visit_log_id_seq', 277, true);


--
-- Name: workflow_inputs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.workflow_inputs_id_seq', 228, true);


--
-- Name: workflow_upload_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.workflow_upload_id_seq', 50, true);


--
-- Name: workflows_id_seq; Type: SEQUENCE SET; Schema: public; Owner: schema
--

SELECT pg_catalog.setval('public.workflows_id_seq', 52, true);


--
-- Name: auth_assignment auth_assignment_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_pkey PRIMARY KEY (item_name, user_id);


--
-- Name: auth_item_child auth_item_child_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item_child
    ADD CONSTRAINT auth_item_child_pkey PRIMARY KEY (parent, child);


--
-- Name: auth_item_group auth_item_group_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item_group
    ADD CONSTRAINT auth_item_group_pkey PRIMARY KEY (code);


--
-- Name: auth_item auth_item_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT auth_item_pkey PRIMARY KEY (name);


--
-- Name: auth_rule auth_rule_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_rule
    ADD CONSTRAINT auth_rule_pkey PRIMARY KEY (name);


--
-- Name: covid_dataset_application covid_dataset_application_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.covid_dataset_application
    ADD CONSTRAINT covid_dataset_application_pkey PRIMARY KEY (id);


--
-- Name: download_dataset download_dataset_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.download_dataset
    ADD CONSTRAINT download_dataset_pkey PRIMARY KEY (id);


--
-- Name: helix_subjects helix_subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.helix_subjects
    ADD CONSTRAINT helix_subjects_pkey PRIMARY KEY (id);


--
-- Name: image_request image_request_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.image_request
    ADD CONSTRAINT image_request_pkey PRIMARY KEY (id);


--
-- Name: migration migration_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.migration
    ADD CONSTRAINT migration_pkey PRIMARY KEY (version);


--
-- Name: notification notification_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT notification_pkey PRIMARY KEY (id);


--
-- Name: ro_crate ro_crate_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ro_crate
    ADD CONSTRAINT ro_crate_pkey PRIMARY KEY (id);


--
-- Name: run_history run_history_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.run_history
    ADD CONSTRAINT run_history_pkey PRIMARY KEY (id);


--
-- Name: software_inputs software_inputs_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software_inputs
    ADD CONSTRAINT software_inputs_pkey PRIMARY KEY (id);


--
-- Name: software software_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software
    ADD CONSTRAINT software_pkey PRIMARY KEY (id);


--
-- Name: software_upload software_upload_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.software_upload
    ADD CONSTRAINT software_upload_pkey PRIMARY KEY (id);


--
-- Name: ticket_body ticket_body_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_body
    ADD CONSTRAINT ticket_body_pkey PRIMARY KEY (id);


--
-- Name: ticket_file ticket_file_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_file
    ADD CONSTRAINT ticket_file_pkey PRIMARY KEY (id);


--
-- Name: ticket_head ticket_head_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_head
    ADD CONSTRAINT ticket_head_pkey PRIMARY KEY (id);


--
-- Name: upload_dataset_defaults upload_dataset_defaults_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_defaults
    ADD CONSTRAINT upload_dataset_defaults_pkey PRIMARY KEY (id);


--
-- Name: upload_dataset_helix upload_dataset_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_helix
    ADD CONSTRAINT upload_dataset_pkey PRIMARY KEY (id);


--
-- Name: upload_dataset_zenodo upload_dataset_zenodo_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.upload_dataset_zenodo
    ADD CONSTRAINT upload_dataset_zenodo_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: user_visit_log user_visit_log_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.user_visit_log
    ADD CONSTRAINT user_visit_log_pkey PRIMARY KEY (id);


--
-- Name: workflow_inputs workflow_inputs_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow_inputs
    ADD CONSTRAINT workflow_inputs_pkey PRIMARY KEY (id);


--
-- Name: workflow_upload workflow_upload_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow_upload
    ADD CONSTRAINT workflow_upload_pkey PRIMARY KEY (id);


--
-- Name: workflow workflows_pkey; Type: CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.workflow
    ADD CONSTRAINT workflows_pkey PRIMARY KEY (id);


--
-- Name: helix_subject_name_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX helix_subject_name_idx ON public.helix_subjects USING btree (name);


--
-- Name: history_start_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX history_start_idx ON public.run_history USING btree (start);


--
-- Name: history_stop_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX history_stop_idx ON public.run_history USING btree (stop);


--
-- Name: i_id_body; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX i_id_body ON public.ticket_file USING btree (id_body);


--
-- Name: i_ticket_body; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX i_ticket_body ON public.ticket_body USING btree (id_head);


--
-- Name: i_ticket_head; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX i_ticket_head ON public.ticket_head USING btree (user_id);


--
-- Name: idx-auth_item-type; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX "idx-auth_item-type" ON public.auth_item USING btree (type);


--
-- Name: lock_id_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX lock_id_idx ON public.operation_locks USING btree (id);


--
-- Name: lock_op_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX lock_op_idx ON public.operation_locks USING btree (operation);


--
-- Name: name_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX name_idx ON public.software USING btree (name);


--
-- Name: name_upload_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX name_upload_idx ON public.software_upload USING btree (name);


--
-- Name: run_history_jobid_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX run_history_jobid_idx ON public.run_history USING btree (jobid);


--
-- Name: run_history_project_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX run_history_project_idx ON public.run_history USING btree (project);


--
-- Name: run_history_status_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX run_history_status_idx ON public.run_history USING btree (status);


--
-- Name: run_history_username_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX run_history_username_idx ON public.run_history USING btree (username);


--
-- Name: softid_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX softid_idx ON public.software_inputs USING btree (softwareid);


--
-- Name: version_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX version_idx ON public.software USING btree (version);


--
-- Name: version_upload_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX version_upload_idx ON public.software_upload USING btree (version);


--
-- Name: workflow_id_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX workflow_id_idx ON public.workflow_inputs USING btree (workflow_id);


--
-- Name: workflow_name_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX workflow_name_idx ON public.workflow USING btree (name);


--
-- Name: workflow_name_upload_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX workflow_name_upload_idx ON public.workflow_upload USING btree (name);


--
-- Name: workflow_version_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX workflow_version_idx ON public.workflow USING btree (version);


--
-- Name: workflow_version_upload_idx; Type: INDEX; Schema: public; Owner: schema
--

CREATE INDEX workflow_version_upload_idx ON public.workflow_upload USING btree (version);


--
-- Name: auth_assignment auth_assignment_item_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_item_name_fkey FOREIGN KEY (item_name) REFERENCES public.auth_item(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_assignment auth_assignment_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_item_child auth_item_child_child_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item_child
    ADD CONSTRAINT auth_item_child_child_fkey FOREIGN KEY (child) REFERENCES public.auth_item(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_item_child auth_item_child_parent_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item_child
    ADD CONSTRAINT auth_item_child_parent_fkey FOREIGN KEY (parent) REFERENCES public.auth_item(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_item auth_item_rule_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT auth_item_rule_name_fkey FOREIGN KEY (rule_name) REFERENCES public.auth_rule(name) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: auth_item fk_auth_item_group_code; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT fk_auth_item_group_code FOREIGN KEY (group_code) REFERENCES public.auth_item_group(code) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: ticket_file fk_id_body; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_file
    ADD CONSTRAINT fk_id_body FOREIGN KEY (id_body) REFERENCES public.ticket_body(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: ticket_body fk_ticket_body; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_body
    ADD CONSTRAINT fk_ticket_body FOREIGN KEY (id_head) REFERENCES public.ticket_head(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: ticket_head fk_ticket_head; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.ticket_head
    ADD CONSTRAINT fk_ticket_head FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_visit_log user_visit_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: schema
--

ALTER TABLE ONLY public.user_visit_log
    ADD CONSTRAINT user_visit_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

