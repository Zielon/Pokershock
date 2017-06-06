#!/usr/bin/env python
from collections import OrderedDict
import operator

import os
os.environ['HOME'] = "/public_html//Pokershock/"

# Function which is creating ordered dictionary as
# cards buckets on appropriate index in dictionary
# @hands list of cards
def createBuckets(hands):

    dictList = []
    for i in hands:
        cardsbuckets = OrderedDict([("Two", 0), ("Three", 0), ("Four", 0), ("Five", 0), ("Six", 0),
                                   ("Seven", 0), ("Eight", 0), ("Nine", 0), ("Ten", 0), ("Jack", 0),
                                   ("Queen", 0), ("King", 0), ("Ace", 0)])
        for card in i.split():
            if "2" in card:
                cardsbuckets["Two"] += 1
            elif "3" in card:
                cardsbuckets["Three"] += 1
            elif "4" in card:
                cardsbuckets["Four"] += 1
            elif "5" in card:
                cardsbuckets["Five"] += 1
            elif "6" in card:
                cardsbuckets["Six"] += 1
            elif "7" in card:
                cardsbuckets["Seven"] += 1
            elif "8" in card:
                cardsbuckets["Eight"] += 1
            elif "9" in card:
                cardsbuckets["Nine"] += 1
            elif "T" in card:
                cardsbuckets["Ten"] += 1
            elif "J" in card:
                cardsbuckets["Jack"] += 1
            elif "Q" in card:
                cardsbuckets["Queen"] += 1
            elif "K" in card:
                cardsbuckets["King"] += 1
            elif "A" in card:
                cardsbuckets["Ace"] += 1

        items = cardsbuckets.items()
        items.reverse()
        dictList.append(OrderedDict(items))

    return dictList


def evaluator(hands):

    selectedCards = []
    dictList = createBuckets(hands)
    for dict in dictList:
        cards = []
        counter = 0
        top = max(dict.iteritems(), key=operator.itemgetter(1))[0]
        cards.append(top + " " + str(dict[top]))
        counter += dict[top]
        for k, v in dict.items():
            if v == 0 or k == top:
                continue

            if counter + v <= 5:
                counter += v
                cards.append(k + " " + str(v))

        selectedCards.append(cards)

    for i in selectedCards:
        print i


def poker(hands):
    scores = [(i, score(hand.split())) for i, hand in enumerate(hands)]
    winner = sorted(scores , key=lambda x:x[1])[-1][0]
    return hands[winner]

def score(hand):
    ranks = '23456789TJQKA'
    rcounts = {ranks.find(r): ''.join(hand).count(r) for r, _ in hand}.items()
    score, ranks = zip(*sorted((cnt, rank) for rank, cnt in rcounts)[::-1])
    if len(score) == 5:
        if ranks[0:2] == (12, 3): #adjust if 5 high straight
            ranks = (3, 2, 1, 0, -1)
        straight = ranks[0] - ranks[4] == 4
        flush = len({suit for _, suit in hand}) == 1
        '''no pair, straight, flush, or straight flush'''
        score = ([1, (3,1,1,1)], [(3,1,1,2), (5,)])[flush][straight]
    return score, ranks




if __name__ == '__main__':

    #print evaluator(['8C TS KC 9H 4S 6S 7H', '7D 2S 5D 3S AC 6S 7H', '8C AD 8D AC 9C 6S 7H', '7C 5H KD KH KS 6S 7H'])
    print "eee"
    # print poker(['8C TS KC 9H 4S', '7D 2S 5D 3S AC', '8C AD 8D AC 9C', '7C 5H KD KH KS'])
