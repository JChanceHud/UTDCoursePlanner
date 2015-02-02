//
//  AppDelegate.m
//  CoursebookTest
//
//  Created by Chance Hudson on 2/2/15.
//  Copyright (c) 2015 Chance Hudson. All rights reserved.
//

#import "AppDelegate.h"

@interface AppDelegate ()

@property IBOutlet NSWindow *window;
@end

@implementation AppDelegate

- (void)applicationDidFinishLaunching:(NSNotification *)aNotification {
    CBScraper *scraper = [[CBScraper alloc] init];
    NSString *result = [scraper search:@"cs2336"];
    NSArray *a = [scraper parseSearch:result];
}

- (void)applicationWillTerminate:(NSNotification *)aNotification {
    // Insert code here to tear down your application
}

@end
