import MySQLdb
import sys
sys.path.append("../../include/python/")
from paper import Paper

def Extrack(buf):
    p1 = buf.find('>')
    key = ''
    value = ''
    p1 = p1 + 1
    p2 = buf.find('<', p1)
    key = buf[0:p1]
    value = buf[p1:p2]
    return [key,value]

def ExtractEndTag(buf):
    p1 = buf.find('</') + 2
    p2 = buf.find('>',p1)
    return buf[p1:p2]

def ExtractStartTag(buf):
    p1 = buf.find('<') + 1
    p2 = buf.find(' ',p1)
    return buf[p1:p2]

def ExtractDBLPKey(buf):
    if buf.find('mdate=') < 0:
        return ''
    p1 = buf.find('key=\"') + len('key=\"')
    p2 = buf.find('\"', p1)
    return buf[p1:p2]

connection = MySQLdb.connect (host = "127.0.0.1", user = "paperlens", passwd = "paper1ens", db = "paperlens")
cursor = connection.cursor()
cursor.execute("truncate table paper;")
cursor.execute("truncate table paper_author;")
connection.commit()
data = open("../../../data/dblp.xml")

item = Paper()
paper_types = set(['article','inproceedings','proceedings','book','incollection','phdthesis','mastersthesis','www']);
author_index = dict()

try:
    n = 0
    for line in data:
        dblp_key = ExtractDBLPKey(line)
        if len(dblp_key) > 0:
            item.dblp_key = dblp_key
        endTag = ExtractEndTag(line);
        if endTag in paper_types and len(item.authors) > 0:
            cursor.execute("insert into paper(id,title,year,booktitle,type,dblp_key,journal,school,publisher) values (%s,%s,%s,%s,%s,%s,%s,%s,%s);",
                           (n, item.title, item.publish_year, item.booktitle, endTag, item.dblp_key,
                            item.journal,item.school,item.publisher))
            author_rank = 0
            for author in item.authors:
                if author not in author_index:
                    author_index[author] = len(author_index)
                cursor.execute("replace into paper_author(paper_id, author_id, rank) values (%s, %s, %s);", (n, author_index[author], author_rank))
                author_rank = author_rank + 1
            n = n + 1
            if n % 10000 == 0:
                print str(n)
            item = Paper()
        else:
            [key,value] = Extrack(line)
            if key == "<author>":
                item.authors.append(value)
            elif key == "<title>":
                item.title = value.strip('.')
            elif key == "<year>":
                item.publish_year = int(value)
            elif key == "<booktitle>":
                item.booktitle = value
            elif key == "<journal>":
                item.journal = value
            elif key == "<school>":
                item.school = value
            elif key == "<cite>":
                item.cites.append(value)
            elif key == "<publisher>":
                item.publisher = value
    for (name,author_id) in author_index.items():
        cursor.execute("insert into author(id, name) values (%s, %s);", (author_id, name))
    connection.commit()
    cursor.close()
    connection.close()
except MySQLdb.Error, e:
    print e.args[0], e.args[1]
