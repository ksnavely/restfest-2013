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

    coffee = attrs['drink-type']

    return coffee
