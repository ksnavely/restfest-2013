"""
coffee_robot.py

I am a coffee robot, I take a work order and make coffee.

Run me:
    python -m coffee_robot
"""

import requests

COLLECTION_URI = "http://10.0.12.137:1234/foo/bar"

coffee_order = { 
    'type': 'restfest-coffee',
    'input': {
        'drink-type' : 'mocha',
        'size': 'small',
        'addons' : [
            { 'type' : 'half and half', 'amount' : '2oz' },
            { 'type' : 'sugar',         'amount' : '1 cube' }
        ]
    }
}

#def look_for_work():
#    items = requests.get(COLLECTION_URI)
#    for i in items:
#        get_do_work(i.href)
#
#def get_do_work(URI):
#    rv = requests.get(URI)
#    wo = rv.json()
#    coffee = make_coffee( wo )
#    return coffee

def make_coffee( work ):
    if work.get('type', None) != 'restfest-coffee':
        raise Exception('Work order must be type "restfest-coffee"')
    if work.get('input', None) is None:
        raise Exception('Work order must specify input')

    attrs = work['input']

    if attrs.get('drink-type', None) is None:
        raise Exception('drink-type must be specified')
    if attrs.get('size', None) is None:
        raise Exception('size must be specified')

    coffee = attrs['size'] + ' ' + attrs['drink-type']

    if attrs.get('addons', None):
        for a in attrs['addons']:
            coffee = coffee + ' ' + a['amount'] + ' of ' + a['type']

    return coffee

if __name__ == '__main__':
    print(make_coffee(coffee_order))
