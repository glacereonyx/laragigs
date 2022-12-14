<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    /**
     * Get and show all listings.
     */
    public function index()
    {
        return view('listings.index', [
            'heading' => 'Latest Listings',
            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6),
        ]);
    }

    /**
     * Show single listing.
     */
    public function show(Listing $listing)
    {
        return view('listings.show', [
            'listing' => $listing,
        ]);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('listings.create');
    }

    /**
     * Store listing data
     */
    public function store()
    {
        $formFields = request()->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);

        if (request()->hasFile('logo')) {
            $formFields['logo'] = request()->file('logo')->store('logos');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfully!');
    }

    /**
     * Show edit form.
     */
    public function edit(Listing $listing)
    {
        return view('listings.edit', ['listing' => $listing]);
    }

    /**
     * Update listing data
     */
    public function update(Listing $listing)
    {
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }


        $formFields = request()->validate([
            'title' => 'required',
            'company' => ($listing->company == request()->company) ? 'required' : ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);


        if (request()->hasFile('logo')) {
            $formFields['logo'] = request()->file('logo')->store('logos');
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing edited successfully!');
    }

    /**
     * Destroy listing
     */
    public function destroy(Listing $listing)
    {
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        $listing->delete();

        return redirect('/')->with('message', 'Deleted successfully.');
    }

    /**
     * Manage listings.
     */
    public function manage()
    {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}