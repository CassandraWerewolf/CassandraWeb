#!/usr/bin/python
# -*- coding: UTF-8 -*-

import os
import sys
import getopt
import datetime
import xml.etree.ElementTree as ET
from urllib2 import urlopen
from sqlalchemy import *
import dateutil.parser

# define an empty class to use like a C struct
# to hold data
class Data:
    pass

# list the xml elements to pull out
data_items = ['username',
            'id',
            'postdate',
            'numedits',
            'editdate',
            ]

# get the id value of the given table row using the given col and value
def get_id(table, col, val):
    s = select([table.c.id], getattr(table.c, col) == val)
    rs = s.execute();
    row = rs.fetchone();
    return row.id

# insert the article into the database by first getting the id's for the
# user and game and then inserting all appropriate data into the Posts table
def insert_article(posts, users, games, article):
    try:
        user_id = get_id(users, 'name', article.username)
        game_id = get_id(games, 'thread_id', article.threadid)
        time_stamp = dateutil.parser.parse(article.postdate).strftime('%Y-%m-%d %H:%M:%S')
        edit_date = dateutil.parser.parse(article.editdate).strftime('%Y-%m-%d %H:%M:%S')
        posts.insert().execute(article_id=article.id,
                            game_id=game_id,
                            user_id=user_id,
                            time_stamp=time_stamp,
                            text=article.body,
                            num_edits=article.numedits,
                            edit_date=edit_date)
    except Exception, err:
        sys.stderr.write('ERROR: %s\n' % str(err))
        return 1

def get_articles(xml, articleid, threadid):
    # create an empty list to contain all the articles and then
    # iterate over the xml to fill it
    data_list = []
    for node in xml:
        data = Data()
        # The data_itmes are attributes of the article node
        for item in data_items:
            setattr(data, item, node.get(item))
        # The body is a child of the article node
        setattr(data, "body", node.find("body").text)

        # add the article to the list if the articleid is greater
        # than the one given on the command line
        if int(getattr(data, 'id')) > int(articleid):
            setattr(data, 'threadid', threadid)
            setattr(data, 'page', 1)
            data_list.append(data)
    return data_list

def main(argv=sys.argv):

    if ( len(sys.argv) != 3 ):
        print
        print "Usage %s thread article" % sys.argv[0]
        print
        quit()

    # the threadid defines which thread to get from bgg and the articleid
    # determines which articles to return (only those numerically after
    # the id that is give, so give a 0 to return all)
    threadid = sys.argv[1]
    articleid = sys.argv[2]
    user = os.getenv('MYSQL_USER')
    password = os.getenv('MYSQL_PASSWORD')
    dbname = os.getenv('MYSQL_DATABASE')
    host = os.getenv('MYSQL_HOST')

    # connect to the database and load in the metadata for auto table
    # definitions used below
    connstr = 'mysql://{user}:{password}@{host}:3306/{dbname}?charset=utf8'.format(user=user, password=password, host=host, dbname=dbname)
    db = create_engine(connstr)
    meta_data = MetaData(db)

    # define the table objects using the metadata
    Users = Table('Users', meta_data, autoload=True)
    Games = Table('Games', meta_data, autoload=True)
    Posts = Table('Posts', meta_data, autoload=True)

    # request the xml from bgg
    url = 'http://boardgamegeek.com/xmlapi2/thread?id=' + threadid + '&minarticleid=' + articleid

    response = urlopen(url)

    # creat the xml iterator around the article element and insert each
    # article into the database
    tree = ET.parse(response)
    root = tree.getroot()
    iter = root.getiterator('article')
    articles = get_articles(iter, articleid, threadid)
    for article in articles:
        insert_article(Posts, Users, Games, article)

if __name__ == "__main__":
    main()
