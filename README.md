# ENews

## Idea

RESTful API serves a news application <br>
Built using ***Laravel*** | ***MySQL*** | ***Redis***


## Features
All data are cached using the write-through strategy that leads to low latency

Cache writing and refreshing is done through queues

There are five types of roles in this application :-
- Admin
  - Manage all website data including updating, deleting, publishing, and hiding the content through the dashboard.
  - Manage clerks and their roles and status like marking as a publisher or ban a clerk from accessing his privileges
- Moderator
    - Manage all website data including updating, deleting, publishing, and hiding the content through the dashboard.
- Writer
  - Write articles and update his ones through the dashboard
  - Publish or unpublish his articles if he has the publisher privilege
- Journalist
    - Write news and update his ones through the dashboard
    - Publish or unpublish his news if he has the publisher privilege
- Guest
  - Access the public data of topics, categories, tags, writers, and journalists

#### Upcoming features
- Subscription system
  - Notify subscribers when a topic they're interested in is published
  - Categories, tags, journalists, or writers could be subscribed


## Requirements
- Laravel >= 9.0
- PHP >= 8.1
- Redis

## License
[MIT license](https://opensource.org/licenses/MIT).

