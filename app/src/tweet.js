/** @jsx React.DOM */
var Tweet = React.createClass({
    render: function() {
        return (
            <li>
                <div className={'tweet-avatar'}>
                    <img className={'avatar'} src={this.props.avatar} align={'left'}/>
                </div>
                <div className={'tweet-content'}>
                    <p className={'tweet-author'}>
                        <a href={'https://twitter.com/' + this.props.screen_name}>{this.props.author_name}</a>
                        <span className={'tweet-nickname'}>@{this.props.screen_name}</span>
                    </p>
                    <p className={'tweet-text'} dangerouslySetInnerHTML={{__html: this.props.text}}>
                    </p>
                </div>
            </li>
        )
    }
});

var TweetList = React.createClass({
    render: function() {
        var tweets = this.props.data.map(function(tweet) {
            return <Tweet text={tweet.text} avatar={tweet.avatar} author_name={tweet.author_name} screen_name={tweet.screen_name} />;
        });
        return (
            <div id={'tweets-content'}>
                <ul className={'tweets'}>
                    {tweets}
                </ul>
            </div>
        )
    }
});

var TweetBox = React.createClass({
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
            <div>
                <TweetList data={this.state.data} />
            </div>
        )
    }
});

React.renderComponent(
  <TweetBox />,
  document.getElementById('tweets')
);

