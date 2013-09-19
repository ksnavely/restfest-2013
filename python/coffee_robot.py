"""
coffee_robot.py

I am a coffee robot, I take a work order and make coffee.

Run me:
    python -m coffee_robot
"""
import time

import requests

BASE_URI = "http://10.0.12.137:1234"
COLLECTION_URI = "http://10.0.12.137:1234/coffee/input-queue"

def look_for_work():
    rv = requests.get(COLLECTION_URI)
    items = rv.json()['collection']['items']
    print("  Found {0} jobs.".format(len(items)))
    for i in items:
        try:
            do_work(i['href'])
        except Exception as ex:
            print("! An exception occurred: {0}".format(str(ex)))
            exit()

def do_work(RESOURCE_URI):
    URI = BASE_URI + RESOURCE_URI
    rv = requests.get(URI)
    wo = rv.json()

    # Validate moar
    validate_wo(wo)
    
    # Start work
    requests.post(BASE_URI+wo['start'], data={"coffee": "Started brewing {0}".format(RESOURCE_URI)})

    print("    Brewing {0}.".format(RESOURCE_URI))
    try:
        coffee = make_coffee( wo )
        print("    Coffee done: " + coffee)
    except:
        requests.post(BASE_URI+wo['fail'], data={"coffee": "Exception: {0}".format(ex)})
        exit()
    
    # Done
    requests.post(BASE_URI+wo['complete'], data={'coffee': coffee})

def validate_wo( work ):
    if work.get('type', None) != 'http://mogsie.com/2013/workflow/restfest-coffee':
        raise Exception('Work order must be type "restfest-coffee"')
    if work.get('input', None) is None:
        raise Exception('Work order must specify input')

    attrs = work['input']

    if attrs.get('drink-type', None) is None:
        raise Exception('drink-type must be specified')
    if attrs.get('size', None) is None:
        raise Exception('size must be specified')

def make_coffee( work ):
    attrs = work['input']

    # Brew moar
    coffee = attrs['size'] + ' ' + attrs['drink-type']
    if attrs.get('addons', None):
        for a in attrs['addons']:
            coffee = coffee + ' ' + a['amount'] + ' of ' + a['type']

    return coffee

if __name__ == '__main__':
    while True:
        print("Looking for work...")
        look_for_work()
        print("No more work, sleeping...")
        time.sleep(30)
