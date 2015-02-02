//
//  CBScraper.m
//  CoursebookTest
//
//  Created by Chance Hudson on 2/2/15.
//  Copyright (c) 2015 Chance Hudson. All rights reserved.
//

#import "CBScraper.h"

@implementation CBScraper

-(NSString*)search:(NSString*)searchString{
    NSURL *searchURL = [NSURL URLWithString:[NSString stringWithFormat:@"%@%@", BASE_SEARCH_URL, searchString]];
    NSData *d = [NSData dataWithContentsOfURL:searchURL];
    return [[[NSString alloc] initWithData:d encoding:NSUTF8StringEncoding] autorelease];
}

-(NSArray*)parseSearch:(NSString*)search{
    HTMLDocument *document = [HTMLDocument documentWithString:search];
    HTMLElement *table = [self findElementWithTag:@"tbody" inDocument:document];
    //go through the rows
    for(HTMLElement *row in table.childElementNodes){
        NSMutableArray *arr = [self getStringsWithinElement:row];
    }
    return nil;
}

-(HTMLElement*)findElementWithTag:(NSString*)tag inDocument:(HTMLDocument*)doc{
    return [self _findElementWithTag:tag currentElement:doc.rootElement];
}

-(HTMLElement*)_findElementWithTag:(NSString*)tag currentElement:(HTMLElement*)element{
    if(element.childElementNodes.count == 0)
        return nil;
    for(HTMLElement *e in element.childElementNodes){
        if([e.tagName isEqualToString:tag])
            return e;
        HTMLElement *s = [self _findElementWithTag:tag currentElement:e];
        if(s) return s;
    }
    return nil;
}

-(NSMutableArray*)getStringsWithinElement:(HTMLElement*)element{
    return [self _getStrings:element arr:[NSMutableArray array]];
}

-(NSMutableArray*)_getStrings:(HTMLElement*)e arr:(NSMutableArray*)arr{
    if(e.numberOfChildren == 0)
        return arr;
    for(int x = 0; x < e.numberOfChildren; x++){
        if([[e childAtIndex:x] isKindOfClass:[HTMLTextNode class]]){
            NSString *s = [(HTMLTextNode*)[e childAtIndex:x] data];
            if(s.length > 0 && ![s isEqualToString:@" "])
                [arr addObject:s];
        }
    }
    for(HTMLElement *ee in e.childElementNodes){
        arr = [self _getStrings:ee arr:arr];
    }
    return arr;
}


@end
