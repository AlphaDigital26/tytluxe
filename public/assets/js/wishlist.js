/**
 * TYT Luxe — Multi-Category Wishlist Manager
 * Handles saving, removing, syncing and live count updates for:
 * - Hotels (Luxury Stays & Sanctuaries)
 * - Packages (Curated Holiday Packages & Itineraries)
 * - Flights (Flight Experiences & Bookings)
 */
(function (window, document) {
  'use strict';

  const STORAGE_KEY = 'tyt_hotel_wishlist';

  const tytWishlist = {
    /**
     * Determine category of an item ('hotel', 'package', 'flight')
     * @param {Object} item
     * @returns {string}
     */
    getItemType: function (item) {
      if (!item) return 'hotel';
      if (item.type && (item.type === 'hotel' || item.type === 'package' || item.type === 'flight')) {
        return item.type;
      }
      const id = String(item.id || '').toLowerCase();
      const slug = String(item.slug || '').toLowerCase();
      const url = String(item.url || '').toLowerCase();

      if (id.startsWith('pkg-') || slug.startsWith('pkg-') || url.includes('/packages')) {
        return 'package';
      }
      if (id.startsWith('flt-') || slug.startsWith('flt-') || url.includes('/flights')) {
        return 'flight';
      }
      return 'hotel';
    },

    /**
     * Get all saved items
     * @returns {Array<Object>}
     */
    get: function () {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const list = raw ? JSON.parse(raw) : [];
        const self = this;
        if (!Array.isArray(list)) return [];
        return list.map(function (it) {
          if (!it.type) {
            it.type = self.getItemType(it);
          }
          return it;
        });
      } catch (e) {
        console.warn('tytWishlist: error parsing storage', e);
        return [];
      }
    },

    /**
     * Get items filtered by category ('hotel' | 'package' | 'flight' | 'all')
     * @param {string} [type='all']
     * @returns {Array<Object>}
     */
    getByType: function (type) {
      const all = this.get();
      if (!type || type === 'all') return all;
      const self = this;
      return all.filter(function (it) {
        return self.getItemType(it) === type;
      });
    },

    /**
     * Save items array to localStorage
     * @param {Array<Object>} items
     */
    save: function (items) {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        this.updateBadges();
        this.syncButtons();
        window.dispatchEvent(new CustomEvent('tyt:wishlist-updated', {
          detail: {
            items: items,
            count: items.length,
            counts: this.counts()
          }
        }));
      } catch (e) {
        console.warn('tytWishlist: error saving to storage', e);
      }
    },

    /**
     * Total number of saved items
     * @returns {number}
     */
    count: function () {
      return this.get().length;
    },

    /**
     * Live counts by category: { total, hotel, package, flight }
     * @returns {Object}
     */
    counts: function () {
      const self = this;
      const all = this.get();
      let hotel = 0, pkg = 0, flight = 0;
      all.forEach(function (it) {
        const t = self.getItemType(it);
        if (t === 'package') pkg++;
        else if (t === 'flight') flight++;
        else hotel++;
      });
      return {
        total: all.length,
        hotel: hotel,
        package: pkg,
        flight: flight
      };
    },

    /**
     * Check if an item is saved by slug or id
     * @param {string|number} identifier
     * @returns {boolean}
     */
    has: function (identifier) {
      if (!identifier) return false;
      const strId = String(identifier).trim().toLowerCase();
      return this.get().some(function (item) {
        return (item.slug && String(item.slug).trim().toLowerCase() === strId) ||
               (item.id && String(item.id).trim().toLowerCase() === strId);
      });
    },

    /**
     * Add item to wishlist
     * @param {Object} itemData
     * @param {boolean} [silent=false]
     */
    add: function (itemData, silent) {
      if (!itemData || (!itemData.slug && !itemData.id)) return;
      const items = this.get();
      const identifier = itemData.slug || itemData.id;
      const type = itemData.type || this.getItemType(itemData);

      if (!this.has(identifier)) {
        let defaultTitle = 'Luxury Stay';
        let defaultUrl = '/hotels';
        if (type === 'package') {
          defaultTitle = 'Holiday Package';
          defaultUrl = itemData.slug ? '/packages/' + itemData.slug : '/packages';
        } else if (type === 'flight') {
          defaultTitle = 'Flight Experience';
          defaultUrl = '/flights';
        } else if (itemData.slug) {
          defaultUrl = '/hotels/' + itemData.slug;
        }

        items.unshift({
          id: itemData.id || '',
          slug: itemData.slug || '',
          type: type,
          title: itemData.title || itemData.name || defaultTitle,
          image: itemData.image || '',
          destination: itemData.destination || itemData.location || (type === 'flight' ? 'All Routes' : 'India'),
          stars: parseInt(itemData.stars || 5, 10),
          price: itemData.price || (type === 'flight' ? 'Best Fare on Enquiry' : 'Price on Request'),
          badge: itemData.badge || '',
          url: itemData.url || defaultUrl
        });
        this.save(items);

        if (!silent && typeof window.showToast === 'function') {
          let categoryName = 'Saved Stays';
          if (type === 'package') categoryName = 'Holiday Packages';
          else if (type === 'flight') categoryName = 'Flight Experiences';

          window.showToast(
            'Saved to Wishlist',
            (itemData.title || 'Item') + ' added to your ' + categoryName + '.',
            'success'
          );
        }
      }
    },

    /**
     * Remove an item from wishlist
     * @param {string|number} identifier
     * @param {boolean} [silent=false]
     */
    remove: function (identifier, silent) {
      if (!identifier) return;
      const strId = String(identifier).trim().toLowerCase();
      const items = this.get();
      let removedTitle = 'Item';

      const filtered = items.filter(function (item) {
        const match = (item.slug && String(item.slug).trim().toLowerCase() === strId) ||
                      (item.id && String(item.id).trim().toLowerCase() === strId);
        if (match && item.title) removedTitle = item.title;
        return !match;
      });

      if (filtered.length !== items.length) {
        this.save(filtered);
        if (!silent && typeof window.showToast === 'function') {
          window.showToast(
            'Removed from Wishlist',
            removedTitle + ' was removed from your wishlist.',
            'success'
          );
        }
      }
    },

    /**
     * Toggle saved status of an item
     * @param {Object} itemData
     * @returns {boolean} true if added, false if removed
     */
    toggle: function (itemData) {
      const identifier = itemData.slug || itemData.id;
      if (this.has(identifier)) {
        this.remove(identifier);
        return false;
      } else {
        this.add(itemData);
        return true;
      }
    },

    /**
     * Clear all saved items across all categories
     */
    clear: function () {
      this.save([]);
      if (typeof window.showToast === 'function') {
        window.showToast('Wishlist Cleared', 'All saved items have been removed.', 'success');
      }
    },

    /**
     * Clear items of a specific category ('hotel' | 'package' | 'flight')
     * @param {string} type
     */
    clearCategory: function (type) {
      if (!type || type === 'all') {
        this.clear();
        return;
      }
      const self = this;
      const remaining = this.get().filter(function (item) {
        return self.getItemType(item) !== type;
      });
      this.save(remaining);
      if (typeof window.showToast === 'function') {
        const label = type === 'package' ? 'Packages' : (type === 'flight' ? 'Flight Experiences' : 'Hotels');
        window.showToast('Category Cleared', 'All saved ' + label + ' have been removed.', 'success');
      }
    },

    /**
     * Update wishlist badge numbers across header dropdown, profile, and mobile navigation
     */
    updateBadges: function () {
      const cnt = this.count();
      const badges = document.querySelectorAll('.tyt-wishlist-badge, #headerWishlistBadge');
      badges.forEach(function (b) {
        b.textContent = cnt;
        if (cnt > 0) {
          b.style.display = 'inline-flex';
        } else {
          b.style.display = 'none';
        }
      });

      const profileCounts = document.querySelectorAll('.mobile-wishlist-count, .tyt-wishlist-count, .profile-dd-badge, .profile-sidebar-wishlist-badge');
      profileCounts.forEach(function (el) {
        el.textContent = cnt;
      });
    },

    /**
     * Sync visual state of all wishlist buttons on the page
     */
    syncButtons: function () {
      const self = this;
      const btns = document.querySelectorAll('.js-wishlist-btn, .htl-heart, .pkg-heart-btn, .flt-heart-btn, #hdFavBtn');
      btns.forEach(function (btn) {
        const slug = btn.getAttribute('data-hotel-slug') || btn.getAttribute('data-slug') || '';
        const id = btn.getAttribute('data-hotel-id') || btn.getAttribute('data-id') || '';
        const isSaved = self.has(slug) || self.has(id);

        if (isSaved) {
          btn.classList.add('active');
          btn.setAttribute('aria-pressed', 'true');
          const txt = btn.querySelector('#hdFavText, .hd-fav-text');
          if (txt) txt.textContent = 'Saved';
        } else {
          btn.classList.remove('active');
          btn.setAttribute('aria-pressed', 'false');
          const txt = btn.querySelector('#hdFavText, .hd-fav-text');
          if (txt) txt.textContent = 'Favourite';
        }
      });
    },

    /**
     * Triggered directly by clicking a wishlist button with data attributes
     * @param {HTMLElement} btn
     * @param {Event} [e]
     */
    toggleFromButton: function (btn, e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      if (!btn) return;

      const id = btn.getAttribute('data-hotel-id') || btn.getAttribute('data-id') || '';
      const slug = btn.getAttribute('data-hotel-slug') || btn.getAttribute('data-slug') || '';
      const rawType = btn.getAttribute('data-type') || btn.getAttribute('data-item-type');
      const type = rawType || (id.startsWith('pkg-') ? 'package' : (id.startsWith('flt-') ? 'flight' : 'hotel'));

      let defaultDestination = 'India';
      if (type === 'flight') defaultDestination = 'All Routes';

      const itemData = {
        id: id,
        slug: slug,
        type: type,
        title: btn.getAttribute('data-hotel-title') || btn.getAttribute('data-title') || '',
        image: btn.getAttribute('data-hotel-image') || btn.getAttribute('data-image') || '',
        destination: btn.getAttribute('data-hotel-destination') || btn.getAttribute('data-destination') || defaultDestination,
        stars: btn.getAttribute('data-hotel-stars') || btn.getAttribute('data-stars') || 5,
        price: btn.getAttribute('data-hotel-price') || btn.getAttribute('data-price') || (type === 'flight' ? 'Best Fare on Enquiry' : 'Price on Request'),
        badge: btn.getAttribute('data-badge') || '',
        url: btn.getAttribute('data-hotel-url') || btn.getAttribute('data-url') || ''
      };

      const added = this.toggle(itemData);
      if (added) {
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');
        const txt = btn.querySelector('#hdFavText, .hd-fav-text');
        if (txt) txt.textContent = 'Saved';
      } else {
        btn.classList.remove('active');
        btn.setAttribute('aria-pressed', 'false');
        const txt = btn.querySelector('#hdFavText, .hd-fav-text');
        if (txt) txt.textContent = 'Favourite';
      }
    },

    /**
     * Initialise on DOM Ready
     */
    init: function () {
      this.updateBadges();
      this.syncButtons();

      // Keep tabs in sync if localStorage changes in another tab
      const self = this;
      window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY) {
          self.updateBadges();
          self.syncButtons();
          window.dispatchEvent(new CustomEvent('tyt:wishlist-updated', {
            detail: { items: self.get(), count: self.count(), counts: self.counts() }
          }));
        }
      });
    }
  };

  // Expose globally
  window.tytWishlist = tytWishlist;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      tytWishlist.init();
    });
  } else {
    tytWishlist.init();
  }
})(window, document);
