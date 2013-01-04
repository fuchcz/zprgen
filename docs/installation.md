# INSTALLATION

## DOWNLOAD

Easiest way to download ZprGen is via Composer. File `composer.json` could look like this:

    {
        "repositories": [
            {
                "type": "vcs",
                "url": "git://github.com/fuchcz/zprgen.git"
            }
        ],
        "require": {
            "fuchcz/zprgen": "dev-master"
        }
    }

## CONNECTING TO BULLETIN BOARD

If you don't use MiniBB with `ffa4minibb`, implement `IBulletinBoard` accordingly.

When user creates a post in defined topic and includes definition `[X|S|7|8]` or generating command (`[zprgen:generate]`), create a ZprGen object e.g.:

    $zprgen = new ZprGen\ZprGen(new ZprGen\MiniBB(array('exec_vlna' => '', 'exec_pdflatex' => '', 'sql_getAllItems' => '')), new ZprGen\ZprGenParser(), __DIR__ . '/files/');

where `exec_vlna` is path to vlna executable (software adding non-breakable spaces), `exec_pdflatex` is path to pfdlatex executable and `sql_getAllItems` is a string containing sql query to get all posts of one issue. Query is processed with sprintf function, with topic id as a parameter (add `%d` where topic id belongs). After that save post:

    $zprgen->savePost($postText, $postId);

or generate whole issue:

    $zprgen->saveAll($topicId, $postId);