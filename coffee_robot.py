coffee_order = { 
    'type': 'restfest-coffee',
    'input': {
        'drink-type' : 'mocha',
        'size': 'small'
    }
}

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

    if attrs['addons']:
        for a in attrs['addons']:
            coffee = coffee + ' ' + a['amount'] + ' of ' + a['type']

    return coffee
