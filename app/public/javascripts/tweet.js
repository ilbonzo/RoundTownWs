/** @jsx React.DOM */
var Tweet = React.createClass({displayName: "Tweet",
    render: function() {
        return (
            React.createElement("li", null, 
                React.createElement("div", {className: 'tweet-avatar'}, 
                    React.createElement("img", {className: 'avatar', src: this.props.avatar, align: 'left'})
                ), 
                React.createElement("div", {className: 'tweet-content'}, 
                    React.createElement("p", {className: 'tweet-author'}, 
                        React.createElement("a", {href: 'https://twitter.com/' + this.props.screen_name}, this.props.author_name), 
                        React.createElement("span", {className: 'tweet-nickname'}, "@", this.props.screen_name)
                    ), 
                    React.createElement("p", {className: 'tweet-text', dangerouslySetInnerHTML: {__html: this.props.text}}
                    )
                )
            )
        )
    }
});

var TweetList = React.createClass({displayName: "TweetList",
    render: function() {
        var tweets = this.props.data.map(function(tweet) {
            return React.createElement(Tweet, {text: tweet.text, avatar: tweet.avatar, author_name: tweet.author_name, screen_name: tweet.screen_name});
        });
        return (
            React.createElement("div", {id: 'tweets-content'}, 
                React.createElement("ul", {className: 'tweets'}, 
                    tweets
                )
            )
        )
    }
});

var TweetBox = React.createClass({displayName: "TweetBox",
    addTweet: function(tweet) {
        var tweets = this.state.data;
        var newTweets = tweets.concat([tweet]);
        // @TODO matteo
        // var tweets = [tweet];
        // var newTweets = tweets.concat(this.state.data);

        if(newTweets.length > 50) {
            newTweets.splice(0, 1);
        }

        this.setState({data: newTweets});
    },
    getInitialState: function() {
        return {data: []};
    },
    componentDidMount: function() {
        var socket = io.connect();
        var self = this;

        socket.on('tweet', function (data) {
            self.addTweet(data);
        });
    },
    render: function() {
        return (
            React.createElement("div", null, 
                React.createElement(TweetList, {data: this.state.data})
            )
        )
    }
});

React.renderComponent(
  React.createElement(TweetBox, null),
  document.getElementById('tweets')
);

