[h3]What is Hubzilla?[/h3]
$Projectname is a [b]free and open source[/b] set of web applications and services running on a special kind of web server, called a "hub", that can connect to other hubs in a decentralized network we like to call "the grid", providing sophisticated communications, identity, and access control services which work together seamlessly across domains and independent websites. It allows anybody to publicly or [b]privately[/b] publish content via "channels", which are the fundamental, cryptographically secured identities that provide authentication independently of the hubs which host them. This revolutionary liberation of online identity from individual servers and domains is called "nomadic identity", and it is powered by the Zot protocol, a new framework for decentralized access control with fine-grained, extensible permissions.

[h3]Right... so what is Hubzilla?[/h3]
From the practical perspective of hub members who use the software, $Projectname offers a variety of familiar, integrated web apps and services, including: 
[ul]
[li]social networking discussion threads[/li]
[li]cloud file storage[/li]
[li]calendar and contacts (with CalDAV and CardDAV support)[/li]
[li]webpage hosting with a content management system[/li]
[li]wiki[/li]
[li]and more...[/li][/ul]
While all of these apps and services can be found in other software packages, only $Projectname allows you to set permissions for groups and individuals who may not even have accounts on your hub! In typical web apps, if you want to share things privately on the internet, the people you share with must have accounts on the server hosting your data; otherwise, there is no robust way for your server to [i]authenticate[/i] visitors to the site to know whether to grant them access. $Projectname solves this problem with an advanced system of [i]remote authentication[/i] that validates the identity of visitors by employing techniques that include public key cryptography.
 
[h3]Software Stack[/h3]
The $Projectname software stack is a relatively standard webserver application written primarily in PHP/MySQL and requiring little more than a web server, a MySQL-compatible database, and the PHP scripting language. It is designed to be easily installable by those with basic website administration skills on typical shared hosting platforms with a broad range of computing hardware. It is also easily extended via plugins and themes and other third-party tools. 

[h3]Additional Resources and Links[/h3]
[list]
[*][url=http://hubzilla.org]Hubzilla project website[/url]
[*][url=https://github.com/redmatrix/hubzilla]Hubzilla core code repository[/url]
[*][url=https://github.com/redmatrix/hubzilla-addons]Hubzilla official addons repository[/url]
[/list]

[h3]Features[/h3]

$Projectname is a general-purpose web publishing and communication network, with several unique features.  It is designed to be used by the widest range of people on the web, from non-technical bloggers, to expert PHP programmers and seasoned systems administrators.

This page lists some of the core features of $Projectname that are bundled with the official release.  As with most free and open source software, there may be many other extensions, additions, plugins, themes and configurations that are limited only by the needs and imagination of the members.

[h4]Affinity Slider[/h4]

When adding connnections in $Projectname, members have the option of assigning "affinity" levels (how close your friendship is) to the new connection.  For example, when adding someone who happens to be a person whose blog you follow, you could assign their channel an affinity level of &quot;Acquaintances&quot;. 

On the other hand, when adding a friend's channel, they could be placed under the affinity level of &quot;Friends&quot;.

At this point, $Projectname [i]Affinity Slider[/i] tool, which usually appears at the top of your &quot;Matrix&quot; page, adjusts the content on the page to include those within the desired affinity range. Channels outside that range will not be displayed, unless you adjust the slider to include them.

The Affinity Slider allows instantaneous filtering of large amounts of content, grouped by levels of closeness.

[h4]Connection Filtering[/h4]

You have the ability to control precisely what appears in your stream using the optional "Connection Filter". When enabled, the Connection Editor provides inputs for selecting criteria which needs to be matched in order to include or exclude a specific post from a specific channel. Once a post has been allowed, all comments to that post are allowed regardless of whether they match the selection criteria. You may select words that if present block the post or ensure it is included in your stream. Regular expressions may be used for even finer control, as well as hashtags or even the detected language of the post.  

[h4]Access Control Lists[/h4]

When sharing content, members have the option of restricting who sees the content.  By clicking on the padlock underneath the sharing box, one may choose desired recipients of the post, by clicking on their names.

Once sent, the message will be viewable only by the sender and the selected recipients.  In other words, the message will not appear on any public walls.

Access Control Lists may be applied to content and posts, photos, events, webpages, chatrooms and files. 

[h4]Single Sign-on[/h4]

Access Control Lists work for all channels in the grid due to our unique single sign-on technology. Most internal links provide an identity token which can be verified on other $Projectname sites and used to control access to private resources. You login once to your home hub. After that, authentication to all $Projectname resources is "magic".


[h4]WebDAV enabled File Storage[/h4]

Files may be uploaded to your personal storage area using your operating system utilities (drag and drop in most cases). You may protect these files with Access Control Lists to any combination of $Projectname members (including some third party network members) or make them public.

[h4]Photo Albums[/h4]

Store photos in albums. All your photos may be protected by Access Control Lists.

[h4]Events Calendar[/h4]

Create and manage events and tasks, which may also be protected with Access Control Lists. Events can be imported/exported to other software using the industry standard vcalendar/iCal format and shared in posts with others. Birthday events are automatically added from your friends and converted to your correct timezone so that you will know precisely when the birthday occurs - no matter where you are located in the world in relation to the birthday person. Events are normally created with attendance counters so your friends and connections can RSVP instantly. 

[h4]Chatrooms[/h4]

You may create any number of personal chatrooms and allow access via Access Control Lists. These are typically more secure than XMPP, IRC, and other Instant Messaging transports, though we also allow using these other services via plugins.       

[h4]Webpage Building[/h4]

$Projectname has many "Content Management" creation tools for building webpages, including layout editing, menus, blocks, widgets, and page/content regions. All of these may be access controlled so that the resulting pages are private to their intended audience. 

[h4]Apps[/h4]

Apps may be built and distributed by members. These are different from traditional "vendor lockin" apps because they are controlled completely by the author - who can provide access control on the destination app pages and charge accordingly for this access. Most apps in $Projectname are free and can be created easily by those with no programming skills. 

[h4]Layout[/h4]

Page layout is based on a description language called Comanche. $Projectname is itself written in Comanche layouts which you can change. This allows a level of customisation you won't typically find in so-called "multi-user environments".

[h4]Bookmarks[/h4]

Share and save/manage bookmarks from links provided in conversations.    
 
 
[h4]Private Message Encryption and Privacy Concerns[/h4]

Private mail is stored in an obscured format. While this is not bullet-proof it typically prevents casual snooping by the site administrator or ISP.  

Each $Projectname channel has it's own unique set of private and associated public RSA 4096-bit keys, generated when the channels is first created. This is used to protect private messages and posts in transit.

Additionally, messages may be created utilising "end-to-end encryption" which cannot be read by $Projectname operators or ISPs or anybody who does not know the passcode. 

Public messages are generally not encrypted in transit or in storage.  

Private messages may be retracted (unsent) although there is no guarantee the recipient hasn't read it yet.

Posts and messages may be created with an expiration date, at which time they will be deleted/removed on the recipient's site.  


[h4]Service Federation[/h4]

In addition to addon "cross-post connectors" to a variety of alternate networks, there is native support for importation of content from RSS/Atom feeds and using this to create special channels. Also, an experimental but working implementation of the Diaspora protocol allows communication with people on the Friendica and Diaspora decentralised social networks. This is currently marked experimental because these networks do not have the same level of privacy and encryption features and abilities as $Projectname and may present privacy risks.

There is also experimental support for OpenID authentication which may be used in Access Control Lists. This is a work in progress. Your $Projectname hub may be used as an OpenID provider to authenticate you to external services which use this technology. 

Channels may have permissions to become "derivative channels" where two or more existing channels combine to create a new topical channel. 

[h4]Privacy Groups[/h4]

Our implementation of privacy groups is similar to Google "Circles" and Diaspora "Aspects". This allows you to filter your incoming stream by selected groups, and automatically set the outbound Access Control List to only those in that privacy group when you post. You may over-ride this at any time (prior to sending the post).  


[h4]Directory Services[/h4]

We provide easy access to a directory of members and provide decentralised tools capable of providing friend "suggestions". The directories are normal $Projectname sites which have chosen to accept the directory server role. This requires more resources than most typical sites so is not the default. Directories are synchronised and mirrored so that they all contain up-to-date information on the entire network (subject to normal propagation delays).  
 

[h4]TLS/SSL[/h4]

For $Projectname hubs that use TLS/SSL, client to server communications are encrypted via TLS/SSL.  Given recent disclosures in the media regarding widespread, global surveillance and encryption circumvention by the NSA and GCHQ, it is reasonable to assume that HTTPS-protected communications may be compromised in various ways. Private communications are consequently encrypted at a higher level before sending offsite.

[h4]Channel Settings[/h4]

When a channel is created, a role is chosen which applies a number of pre-configured security and privacy settings. These are chosen for best practives to maintain privacy at the requested levels.  

If you choose a "custom" privacy role, each channel allows fine-grained permissions to be set for various aspects of communication.  For example, under the &quot;Security and Privacy Settings&quot; heading, each aspect on the left side of the page, has six (6) possible viewing/access options, that can be selected by clicking on the dropdown menu. There are also a number of other privacy settings you may edit.  

The options are:

 - Nobody except yourself.
 - Only those you specifically allow.
 - Anybody in your address book.
 - Anybody on this website.
 - Anybody in this network.
 - Anybody authenticated.
 - Anybody on the Internet.


[h4]Public and Private Forums[/h4]

Forums are typically channels which may be open to participation from multiple authors. There are currently two mechanisms to post to forums: 1) "wall-to-wall" posts and 2) via forum @mention tags. Forums can be created by anybody and used for any purpose. The directory contains an option to search for public forums. Private forums can only be posted to and often only seen by members.


[h4]Account Cloning[/h4]

Accounts in $Projectname are referred to as [i]nomadic identities[/i], because a member's identity is not bound to the hub where the identity was originally created.  For example, when you create a Facebook or Gmail account, it is tied to those services.  They cannot function without Facebook.com or Gmail.com.  

By contrast, say you've created a $Projectname identity called [b]tina@$Projectnamehub.com[/b].  You can clone it to another $Projectname hub by choosing the same, or a different name: [b]liveForever@Some$ProjectnameHub.info[/b]

Both channels are now synchronized, which means all your contacts and preferences will be duplicated on your clone.  It doesn't matter whether you send a post from your original hub, or the new hub.  Posts will be mirrored on both accounts.

This is a rather revolutionary feature, if we consider some scenarios:

 - What happens if the hub where an identity is based suddenly goes offline?  Without cloning, a member will not be able to communicate until that hub comes back online (no doubt many of you have seen and cursed the Twitter "Fail Whale").  With cloning, you just log into your cloned account, and life goes on happily ever after. 

 - The administrator of your hub can no longer afford to pay for his free and public $Projectname hub. He announces that the hub will be shutting down in two weeks.  This gives you ample time to clone your identity(ies) and preserve your$Projectname relationships, friends and content.

 - What if your identity is subject to government censorship?  Your hub provider may be compelled to delete your account, along with any identities and associated data.  With cloning, $Projectname offers [b]censorship resistance[/b].  You can have hundreds of clones, if you wanted to, all named different, and existing on many different hubs, strewn around the internet.  

$Projectname offers interesting new possibilities for privacy. You can read more at the &lt;&lt;Private Communications Best Practices&gt;&gt; page.

Some caveats apply. For a full explanation of identity cloning, read the &lt;HOW TO CLONE MY IDENTITY&gt;.

[h4]Multiple Profiles[/h4]

Any number of profiles may be created containing different information and these may be made visible to certain of your connections/friends. A "default" profile can be seen by anybody and may contain limited information, with more information available to select groups or people. This means that the profile (and site content) your beer-drinking buddies see may be different than what your co-workers see, and also completely different from what is visible to the general public. 

[h4]Account Backup[/h4]

Red offers a simple, one-click account backup, where you can download a complete backup of your profile(s).  

Backups can then be used to clone or restore a profile.

[h4]Account Deletion[/h4]

Accounts can be immediately deleted by clicking on a link. That's it.  All associated content is then deleted from the grid (this includes posts and any other content produced by the deleted profile). Depending on the number of connections you have, the process of deleting remote content could take some time but it is scheduled to happen as quickly as is practical.

[h4]Content Creation[/h4]

[h4]Writing Posts[/h4]

$Projectname supports a number of different ways of adding rich-text content. The default is a custom variant of BBcode, tailored for use in $Projectname. You may also enable the use of Markdown if you find that easier to work with. A visual editor may also be used. The traditional visual editor for $Projectname had some serious issues and has since been removed. We are currently looking for a replacement. 

When creating &quot;Websites&quot;, content may be entered in HTML, Markdown, BBcode, and/or plain text.

[h4]Deletion of content[/h4]
Any content created in $Projectname remains under the control of the member (or channel) that originally created it.  At any time, a member can delete a message, or a range of messages.  The deletion process ensures that the content is deleted, regardless of whether it was posted on a channel's primary (home) hub, or on another hub, where the channel was remotely authenticated via Zot ($Projectname communication and authentication protocol).

[h4]Media[/h4]
Similar to any other modern blogging system, social network, or a micro-blogging service, $Projectname supports the uploading of files, embedding of videos, linking web pages.

[h4]Previewing/Editing[/h4] 
Post can be previewed prior to sending and edited after sending.

[h4]Voting/Consensus[/h4]
Posts can be turned into "consensus" items which allows readers to offer feedback, which is collated into "agree", "disagree", and "abstain" counters. This lets you gauge interest for ideas and create informal surveys. 


[h4]Extending $Projectname[/h4]

$Projectname can be extended in a number of ways, through site customisation, personal customisation, option setting, themes, and addons/plugins. 

[h4]API[/h4]

An API is available for use by third-party services. This is based originally on the early Twitter API (for which hundreds of third-party tools exist). It is currently being extended to provide access to facilities and abilities which are specific to $Projectname. Access may be provided by login/password or OAuth and client registration of OAuth applications is provided.

 


[h3]What is Zot?[/h3]

Zot is the protocol that powers $Projectname, providing three core capabilities: Communications, Identity, and Access Control.

The functionality it provides can also be described as follows: 

 - a relationship online is just a bunch of permissions
 - the internet is just another folder

[h4]Communications[/h4]

Zot is a revolutionary protocol which provides [i]decentralised communications[/i] and [i]identity management[/i] across the grid. The resulting platform can provide web services comparable to those offered by large corporate providers, but without the large corporate provider and their associated privacy issues, insatiable profit drive, and walled-garden mentality.

Communications and social networking are an integral part of the grid. Any channel (and any services provided by that channel) can make full use of feature-rich social communications on a global scale. These communications may be public or private - and private communications comprise not only fully encrypted transport, but also encrypted storage to help protect against accidental snooping and disclosure by rogue system administrators and internet service providers. 

Zot allows a wide array of background services in the grid, from offering friend suggestions, to directory services. You can also perform other things which would typically only be possibly on a centralized provider - such as &quot;Wall to Wall&quot; posts. Private/multiple profiles can be easily created, and web content can be tailored to the viewer via the [i]Affinity Slider[/i]. 

You won't find these features at all on other decentralized communication services. In addition to providing hub (server) decentralization, perhaps the most innovative and interesting Zot feature is its provision of [i]decentralized identity[/i] services.

[h4]Identity[/h4] 

Zot's identity layer is unique. It provides [i]invisible single sign-on[/i] across all sites in the grid. 

It also provides [i]nomadic identity[/i], so that your communications with friends, family, and or anyone else you're communicating with won't be affected by the loss of your primary communication node - either temporarily or permanently. 

The important bits of your identity and relationships can be backed up to a thumb drive, or your laptop, and may appear at any node in the grid at any time - with all your friends and preferences intact. 

Crucially, these nomadic instances are kept in sync so any instance can take over if another one is compromised or damaged. This protects you against not only major system failure, but also temporary site overloads and governmental manipulation or censorship. 

Nomadic identity, single sign-on, and $Projectname's decentralization of hubs, we believe, introduce a high degree of degree of [i]resiliency[/i] and [i]persistence[/i] in internet communications, that are sorely needed amidst global trends towards corporate centralization, as well as mass and indiscriminate government surveillance and censorship.

As you browse the grid, viewing channels and their unique content, you are seamlessly authenticated as you go, even across completely different server hubs. No passwords to enter. Nothing to type. You're just greeted by name on every new site you visit. 

How does Zot do that? We call it [i]magic-auth[/i], because $Projectname hides the details of the complexities that go into single sign-on logins, and nomadic identities, from the experience of browsing on the grid.  This is one of the design goals of $Projectname: to increase privacy, and freedom on the web, while reducing the complexity and tedium brought by the need to enter new passwords and login names for every different sight that someone might visit online.

You login only once on your home hub (or any nomadic backup hub you have chosen). This allows you to access any authenticated services provided anywhere in the grid - such as shopping, blogs, forums, and access to private information. This is just like the services offered by large corporate providers with huge user databases; however you can be a member of this community, as well as a server on this network using a $35 Rasberry Pi. Your password isn't stored on a thousand different sites, or even worse, only on a few sites like Google and Facebook, beyond your direct control.

You cannot be silenced. You cannot be removed from the grid, unless you yourself choose to exit it.

[h4]Access Control[/h4]

Zot's identity layer allows you to provide fine-grained permissions to any content you wish to publish - and these permissions extend across $Projectname. This is like having one super huge website made up of an army of small individual websites - and where each channel in the grid can completely control their privacy and sharing preferences for any web resources they create. 

Currently, the grid supports communications, photo albums, events, and files. This will be extended in the future to provide content management services (web pages) and cloud storage facilities, such as WebDAV and multi-media libraries. Every object and how it is shared and with whom is completely under your control.

This type of control is available on large corporate providers such as Facebook and Google, because they own the user database. Within the grid, there is no need for a huge user database on your machine - because the grid [i]is[/i] your user database. It has what is essentially infinite capacity (limited by the total number of hubs online across the internet), and is spread amongst hundreds, and potentially millions of computers. 

Access can be granted or denied for any resource, to any channel, or any group of channels; anywhere within the grid. Others can access your content if you permit them to do so, and they do not even need to have an account on your hub. Your private photos cannot be viewed, because permission really work; they are not an addon that was added as an afterthought. If you aren't on the list of allowed viewers for a particular photo, you aren't going to look at it. 