//
//  CBScraper.h
//  CoursebookTest
//
//  Created by Chance Hudson on 2/2/15.
//  Copyright (c) 2015 Chance Hudson. All rights reserved.
//

#import <Foundation/Foundation.h>
#import <HTMLReader/HTMLReader.h>
#import <HTMLReader/HTMLTextNode.h>

#define BASE_SEARCH_URL @"http://coursebook.utdallas.edu/search/"

@interface CBScraper : NSObject

-(NSString*)search:(NSString*)searchString;
-(NSArray*)parseSearch:(NSString*)search;

@end
