#!/usr/bin/env python3
# -*- coding: UTF-8 -*-

import os
import sys
import datetime
import xml.etree.ElementTree as ET
from urllib.request import urlopen, Request
from sqlalchemy import *
import dateutil.parser

# Use PyMySQL-compatible connection (install pymysql in your environment)

# define an empty class to use like a C struct
# to hold data
class Data:
    pass

# list the xml elements to pull out
data_items = [
    'username',
    'id',
    'postdate',
    'numedits',
    'editdate',
]


# get the id value of the given table row using the given col and value
def get_id(table, col, val):
    # SQLAlchemy 1.4+ style: build a select and execute on a connection
    s = select(table.c.id).where(getattr(table.c, col) == val)
    with db.connect() as conn:
        rs = conn.execute(s)
        row = rs.fetchone()
    # row may be a Row; return first element
    return row[0] if row is not None else None


# insert the article into the database by first getting the id's for the
# user and game and then inserting all appropriate data into the Posts table
def insert_article(posts, users, games, article):
    try:
        user_id = get_id(users, 'name', article.username)
        game_id = get_id(games, 'thread_id', article.threadid)
        time_stamp = dateutil.parser.parse(article.postdate).strftime('%Y-%m-%d %H:%M:%S')
        edit_date = dateutil.parser.parse(article.editdate).strftime('%Y-%m-%d %H:%M:%S')
        values = {
            'article_id': article.id,
            'game_id': game_id,
            'user_id': user_id,
            'time_stamp': time_stamp,
            'text': article.body,
            'num_edits': article.numedits,
            'edit_date': edit_date,
        }
        # Use a transaction to execute the insert (SQLAlchemy 1.4+)
        with db.begin() as conn:
            conn.execute(posts.insert(), values)
    except Exception as err:
        sys.stderr.write('ERROR: %s\n' % str(err))
        return 1


def get_articles(xml_iter, articleid, threadid):
    # create an empty list to contain all the articles and then
    # iterate over the xml to fill it
    data_list = []
    for node in xml_iter:
        data = Data()
        # The data_items are attributes of the article node
        for item in data_items:
            setattr(data, item, node.get(item))
        # The body is a child of the article node
        body_node = node.find('body')
        setattr(data, 'body', body_node.text if body_node is not None else '')

        # add the article to the list if the articleid is greater
        # than the one given on the command line
        if int(getattr(data, 'id')) > int(articleid):
            setattr(data, 'threadid', threadid)
            setattr(data, 'page', 1)
            data_list.append(data)
    return data_list


def main(argv=None):
    if argv is None:
        argv = sys.argv

    if len(argv) != 3:
        print()
        print('Usage %s thread article' % argv[0])
        print()
        sys.exit(1)

    # the threadid defines which thread to get from bgg and the articleid
    # determines which articles to return (only those numerically after
    # the id that is give, so give a 0 to return all)
    threadid = argv[1]
    articleid = argv[2]
    user = os.getenv('MYSQL_USER')
    password = os.getenv('MYSQL_PASSWORD')
    dbname = os.getenv('MYSQL_DATABASE')
    host = os.getenv('MYSQL_HOST')

    # connect to the database and load in the metadata for auto table
    # definitions used below
    # Use pymysql as the DBAPI (install PyMySQL and SQLAlchemy in the runtime)
    connstr = 'mysql+pymysql://{user}:{password}@{host}:3306/{dbname}?charset=utf8'.format(
        user=user, password=password, host=host, dbname=dbname
    )
    # Expose the engine at module scope so helper functions can use it
    global db
    db = create_engine(connstr)
    meta_data = MetaData()

    # define the table objects using the metadata (autoload with engine)
    Users = Table('Users', meta_data, autoload_with=db)
    Games = Table('Games', meta_data, autoload_with=db)
    Posts = Table('Posts', meta_data, autoload_with=db)

    # request the xml from bgg
    url = 'http://boardgamegeek.com/xmlapi2/thread?id=' + threadid + '&minarticleid=' + articleid

    # If a BGG API token is provided, include it as a Bearer token in the
    # Authorization header. This prevents 401 Unauthorized responses from
    # endpoints that require application auth.
    bgg_token = os.getenv('BGG_API_TOKEN')
    if bgg_token:
        req = Request(url, headers={
            'Authorization': f'Bearer {bgg_token}',
            'User-Agent': 'CassandraWerewolf/1.0'
        })
        response = urlopen(req)
    else:
        response = urlopen(url)

    # create the xml iterator around the article element and insert each
    # article into the database
    tree = ET.parse(response)
    root = tree.getroot()
    xml_iter = root.iter('article')
    articles = get_articles(xml_iter, articleid, threadid)
    for article in articles:
        insert_article(Posts, Users, Games, article)


if __name__ == '__main__':
    main()
