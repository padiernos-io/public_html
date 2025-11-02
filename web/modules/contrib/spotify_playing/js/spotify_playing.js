(function (Drupal) {

    const ttt = (s, d) => {
        var p;
        for (p in d) {
            if (d.hasOwnProperty(p)) {
                s = s.replace(new RegExp('{' + p + '}', 'g'), d[p]);
            }
        }
        return s;
    };

    const spotify = {

    $el: '',
    endpoint: '',
    interval: '',
    playingKey: '',
    template: ``,

        init($el) {
            if (typeof $el !== 'undefined') {
                this.$el = $el;
                this.endpoint = this.$el.getAttribute('data-endpoint');
                this.getNowPlaying();
                this.interval = setInterval(this.getNowPlaying.bind(this), 10000);
            }
        },

        getNowPlaying() {

      if (typeof this.endpoint === 'undefined' || this.endpoint === '') {
        throw new Error(`Endpoint not found.`);
      } else {

                fetch(this.endpoint)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        return response.json();
                    })
                    .then((data) => {

                        const now = data.now;
                        if (typeof now.item !== 'undefined') {
                            const item = now.item;

                            const progress = now.progress_ms;
                            const duration = item.duration_ms;

                            const percentage = (progress / duration) * 100;
                            const not_played = duration - progress;
                            const remaining = ((duration - (duration * (progress / duration))) / 1000);

                            if (!now.is_playing) {
                                this.$el.classList.add('paused');
                            }
                            else {
                                this.$el.classList.remove('paused');
                            }

                            this.$el.classList.add('loaded');

                            if (this.playingKey !== item.id) {
                                this.playingKey = item.id;

                                let artists = [];
                                for (let artist in item.artists) {
                                    artists.push(item.artists[artist].name);
                                }

                                let progress = ttt(`<div id="spotify-progress" class="spotify__progress" style="--animation-width: {aw}; --animation-length: {al}; --animation-state: {state}"></div>`, {
                                    'aw': percentage + '%',
                                    'al': (not_played / 1000) + 's',
                                    'state': (!now.is_playing) ? 'paused' : 'running'
                                });

                                this.$el.innerHTML = ttt(this.template, {
                                    'imgsrc': item.album.images[1].url,
                                    'album': item.album.name,
                                    'songlink': item.external_urls.spotify,
                                    'title': item.name,
                                    'artist': artists.join(', '),
                                    'progress': progress,
                                });

                                let img = this.$el.querySelector('img');
                                if (img) {
                                    img.setAttribute('src', img.getAttribute('data-src'));
                                }
                            }
                            else {
                                document.getElementById('spotify-progress').setAttribute('style', this.generateAnimationProperties(
                                    percentage,
                                    remaining,
                                    (!now.is_playing) ? 'paused' : 'running'
                                ));

                                let animations = document.getAnimations();
                                for (let a in animations) {
                                    if (animations[a].animationName === 'spotify-progress') {
                                        animations[a].cancel();
                                        animations[a].play();
                                    }
                                }
                            }
                        }
                    });

            }

        },

        generateAnimationProperties(w, l, s) {
            return ttt('--animation-width: {aw}; --animation-length: {al}; --animation-state: {state}', {
                'aw': w + '%',
                'al': l + 's',
                'state': s
            });
        }

    };

    Drupal.behaviors.spotify_playing = {
        attach: function (context, settings) {

            const $el = context.querySelector('.spotify');

            spotify.template = `${$el.innerHTML}`;
            $el.innerHTML = '';

            spotify.init($el);

        }
    }

})(Drupal || (Drupal = {}));
